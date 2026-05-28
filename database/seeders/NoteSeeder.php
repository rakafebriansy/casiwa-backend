<?php

namespace Database\Seeders;

use App\Models\Note;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class NoteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $publicPdfsPath = public_path('seed_pdfs');
        if (!File::exists($publicPdfsPath)) {
            $this->command->warn('Directory public/seed_pdfs does not exist.');
            return;
        }

        $files = File::files($publicPdfsPath);
        $user = User::first();
        $userId = $user ? $user->id : 1;

        // Ensure directories exist
        Storage::disk('local')->makeDirectory('pdfs');
        Storage::disk('local')->makeDirectory('thumbnails');

        // Create a dummy thumbnail if it doesn't exist
        $dummyThumbnail = 'default_thumbnail.png';
        if (!Storage::disk('local')->exists("thumbnails/$dummyThumbnail")) {
            Storage::disk('local')->put("thumbnails/$dummyThumbnail", '');
        }

        foreach ($files as $file) {
            if ($file->getExtension() !== 'pdf') {
                continue;
            }

            $originalName = $file->getFilename();
            $nameWithoutExt = pathinfo($originalName, PATHINFO_FILENAME);
            
            // Unique file name to avoid collisions
            $uniqueId = uniqid(mt_rand(), true);
            $fileName = $uniqueId . '.pdf';
            $thumbnailName = $uniqueId . '.png';

            // Copy to storage
            Storage::disk('local')->put("pdfs/$fileName", File::get($file));

            // Generate thumbnail using sips (macOS specific)
            $pdfPath = storage_path("app/pdfs/$fileName");
            $thumbnailPath = storage_path("app/thumbnails/$thumbnailName");
            exec("sips -s format png " . escapeshellarg($pdfPath) . " --out " . escapeshellarg($thumbnailPath) . " > /dev/null 2>&1");

            $note = new Note();
            // Truncate title to 60 characters as defined in the migration
            $note->title = Str::limit($nameWithoutExt, 55, '...');
            $note->description = 'Seeded PDF from root: ' . $originalName;
            $note->file_name = $fileName;
            
            // If sips failed or not on Mac, fallback to dummy
            if (File::exists($thumbnailPath)) {
                $note->thumbnail_name = $thumbnailName;
            } else {
                $note->thumbnail_name = $dummyThumbnail;
            }

            $note->price = 2500;
            $note->user_id = $userId;
            $note->save();

            $this->command->info("Seeded: {$originalName} with thumbnail");
        }
    }
}
