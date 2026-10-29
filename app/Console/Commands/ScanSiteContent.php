<?php

namespace App\Console\Commands;

use App\Models\Site;
use Illuminate\Console\Command;
use Laravel\Ai\Enums\Lab;

use function Laravel\Ai\agent;

class ScanSiteContent extends Command
{
    protected $signature = 'sites:scan-content {--site=} {--max-chars=8000}';

    protected $description = 'Scan site text content for unsafe or inappropriate content using the Laravel AI SDK';

    public function handle(): int
    {
        $providerName = strtolower(env('AI_SCAN_PROVIDER', config('ai.default', 'ollama')));
        $lab = Lab::tryFrom($providerName);

        if ($lab === null) {
            $this->error('Unsupported AI provider: '.$providerName);

            return self::FAILURE;
        }

        $model = env('AI_SCAN_MODEL') ?: env('OLLAMA_MODEL');
        $maxChars = (int) $this->option('max-chars');
        $query = Site::query();

        if ($this->option('site')) {
            $query->where('slug', $this->option('site'));
        }

        foreach ($query->get() as $site) {
            $text = $this->collectText($site, $maxChars);

            if ($text === '') {
                $this->warn($site->slug.': no scannable text files');

                continue;
            }

            try {
                $response = agent(
                    instructions: 'You are a content safety scanner. Analyze the provided website text and decide whether it is safe for public hosting. Reply with exactly one word: SAFE or UNSAFE.',
                )->prompt($text, provider: $lab, model: $model ?: null, timeout: 120);

                $result = trim((string) $response->text);
                $this->info($site->slug.': '.$result);
            } catch (\Throwable $e) {
                $this->error($site->slug.': scan failed - '.$e->getMessage());
            }
        }

        return self::SUCCESS;
    }

    protected function collectText(Site $site, int $maxChars): string
    {
        $base = storage_path('app/private/'.$site->storage_path);
        $allowed = ['html', 'htm', 'txt', 'md', 'css', 'js', 'json', 'xml'];
        $chunks = [];
        $length = 0;

        if (! is_dir($base)) {
            return '';
        }

        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($base, \RecursiveDirectoryIterator::SKIP_DOTS),
        );

        foreach ($iterator as $file) {
            if (! $file->isFile()) {
                continue;
            }

            $extension = strtolower(pathinfo($file->getPathname(), PATHINFO_EXTENSION));
            if (! in_array($extension, $allowed, true)) {
                continue;
            }

            $contents = @file_get_contents($file->getPathname());
            if ($contents === false) {
                continue;
            }

            // Strip HTML tags to reduce noise for text-only scanning.
            $contents = strip_tags($contents);
            $remaining = $maxChars - $length;

            if ($remaining <= 0) {
                break;
            }

            $chunk = mb_substr($contents, 0, $remaining);
            $chunks[] = $chunk;
            $length += strlen($chunk);
        }

        return implode("\n\n---\n\n", $chunks);
    }
}
