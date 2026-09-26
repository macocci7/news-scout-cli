<?php

namespace App\Ai\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Attributes\UseCheapestModel;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Messages\Message;
use Laravel\Ai\Promptable;
use Laravel\Ai\Providers\Tools\ProviderTool;
use Laravel\Ai\Providers\Tools\WebSearch;
use Stringable;

#[UseCheapestModel]
class ScoutNewsAgent implements Agent, Conversational, HasTools, HasStructuredOutput
{
    use Promptable;

    protected string $instructions = 'あなたはニュース記事を収集するアシスタントです。';

    /**
     * @param array{city: string, region: string, country: string} $location
     */
    public function __construct(
        protected array $location,
    ) {
    }

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        return $this->instructions;
    }

    public function setInstructions(string $instructions): self
    {
        $this->instructions = $instructions;
        return $this;
    }

    /**
     * Get the list of messages comprising the conversation so far.
     *
     * @return Message[]
     */
    public function messages(): iterable
    {
        return [];
    }

    /**
     * Get the tools available to the agent.
     *
     * @return list<Agent|Tool|ProviderTool>
     */
    public function tools(): iterable
    {
        return [
            (new WebSearch)->location(...$this->location),
        ];
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'news' => $schema->array(fn (JsonSchema $schema) => [
                'topic' => $schema->string()->description('The topic of the news item')->required(),
                'date' => $schema->string()->description('The publication date of the news item')->required(),
                'source' => $schema->string()->description('The publisher of the news item')->required(),
                'title' => $schema->string()->description('The title of the news item')->required(),
                'summary' => $schema->string()->description('The description of the news item')->required(),
                'url' => $schema->string()->description('The URL of the news item')->required(),
            ])->description('The list of news items')->required(),
            'count' => $schema->integer()->description('The total number of news items')->required(),
        ];
    }
}
