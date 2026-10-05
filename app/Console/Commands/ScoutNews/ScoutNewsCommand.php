<?php

namespace App\Console\Commands\ScoutNews;

use App\ScoutNews\ScoutNewsLogic;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Laravel\Ai\Enums\Lab;

use function Laravel\Prompts\spin;

#[Signature('scout:news
             {provider? : AIプロバイダー 例: openai, gemini}
             {model? : AIモデル名}
             {--topic=* : トピック指定}
             {--add-topic=* : デフォルトと併せて追加指定するトピック}
             {--days= : 過去何日分のニュースを取得するか}
             {--max= : 最大取得件数}
             {--location= : 地域指定。city/region/country 例: Shinjuku/Tokyo/JP}')]
#[Description('ニュースを収集します')]
class ScoutNewsCommand extends Command
{
    public function handle()
    {
        (new ScoutNewsLogic(
            provider: $this->getProvider(),
            model: $this->getModel(),
            topics: $this->getTopics(),
            days: $this->getDays(),
            maxResults: $this->getMaxResults(),
            location: $this->getLocation(),
        ))->run();
    }

    protected function getProvider(): ?Lab
    {
        $provider = $this->argument('provider');
        if (empty($provider)) {
            return null;
        }
        $enum = Lab::tryFrom(strtolower($provider));
        if ($enum === null) {
            throw new \InvalidArgumentException("Unsupported provider: $provider");
        }
        return $enum;
    }

    protected function getModel(): ?string
    {
        return $this->argument('model');
    }

    protected function getTopics(): array
    {
        $topics = $this->option('topic');   // トピック指定
        $addTopics = $this->option('add-topic');    // 追加トピック指定
        $topics = empty($topics) ? config('news.default.topics') : $topics;
        return empty($addTopics) ? $topics : array_merge($topics, $addTopics);
    }

    protected function getDays(): int
    {
        $days = $this->option('days');
        $days = empty($days) ? config('news.default.days') : (int) $days;
        if ($days < 1) {
            throw new \InvalidArgumentException("Days must be at least 1");
        }
        return $days;
    }

    protected function getMaxResults(): int
    {
        $maxResults = $this->option('max');
        $maxResults = empty($maxResults) ? config('news.default.max_results') : (int) $maxResults;
        $upperLimit = config('news.limit.max_results');
        if ($maxResults < 1 || $maxResults > $upperLimit) {
            throw new \InvalidArgumentException("Max results must be between 1 and {$upperLimit}");
        }
        return $maxResults;
    }

    protected function getLocation(): array
    {
        $location = $this->option('location');
        if ($location) {
            $parts = explode('/', $location);
            if (count($parts) !== 3 || empty($parts[0]) || empty($parts[1]) || empty($parts[2])) {
                throw new \InvalidArgumentException("Location must be in the format city/region/country");
            }
            return ['city' => $parts[0], 'region' => $parts[1], 'country' => $parts[2]];
        }
        return config('news.default.location');
    }
}
