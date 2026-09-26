<?php

namespace App\Console\Commands\ScoutNews;

use App\Ai\Agents\ScoutNewsAgent;
use Laravel\Ai\Enums\Lab;
use Illuminate\Support\Carbon;
use Laravel\Ai\Responses\StructuredAgentResponse;

use function Laravel\Prompts\{error, info, spin};

class ScoutNewsLogic
{
    public function __construct(
        protected ?Lab $provider = null,
        protected ?string $model = null,
        protected array $topics,
        protected int $days,
        protected int $maxResults,
        protected array $location,
    ) {
    }

    public function run()
    {
        echo view('scout-news.messages.starting', [
            'topics' => $this->topics,
            'days' => $this->days,
            'maxResults' => $this->maxResults,
            'location' => $this->location,
        ]) . PHP_EOL;
        $response = spin(
            callback: fn () => (new ScoutNewsAgent($this->location))
                ->setInstructions(view('scout-news.instructions.scout-news'))
                ->prompt(view('scout-news.prompts.scout-news', [
                        'topics' => $this->topics,
                        'days' => $this->days,
                        'maxResults' => $this->maxResults,
                    ]),
                    provider: $this->provider,
                    model: $this->model,
                    timeout: 120,
                ),
            message: 'ニュース記事を収集中です。。',
        );
        $this->storeNews($response);
    }

    protected function storeNews(StructuredAgentResponse $response): void
    {
        $datetime = Carbon::now('Asia/Tokyo')->format("Ymd_His");

        // JSON保存
        $filePath = storage_path('app/private/news_' . $datetime . '.json');
        $isSaved = file_put_contents($filePath, json_encode($response, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        if ($isSaved === false) {
            error("⚠️ JSON保存失敗: $filePath");
        } else {
            info("✅ JSON保存成功: $filePath");
        }

        // トピックごとに記事を整理
        $articles = [];
        foreach ($response['news'] as $article) {
            $topic = $article['topic'];
            if (!isset($articles[$topic])) {
                $articles[$topic] = [];
            }
            $articles[$topic][] = $article;
        }
        // トピックごとの記事数を集計
        $topicsCount = [];
        foreach ($articles as $topic => $items) {
            $topicsCount[$topic] = count($items);
        }
        // Markdown生成
        $markdown = view('scout-news.markdown.output', [
            'articles' => $articles,
            'topicsCount' => $topicsCount,
            'generatedAt' => Carbon::now('Asia/Tokyo')->format("Y年m月d日 H時i分"),
            'count' => count($response['news']),
            'location' => $this->location,
            'provider' => $this->provider,
            'model' => $this->model,
        ]);
        // Markdown保存
        $filePath = storage_path('app/public/news_' . $datetime . '.md');
        $isSaved = file_put_contents($filePath, $markdown);
        if ($isSaved === false) {
            error("⚠️ Markdown保存失敗: $filePath");
        } else {
            info("✅ Markdown保存成功: $filePath");
        }
    }
}
