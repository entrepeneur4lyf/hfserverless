<?php

namespace Tests;

use App\HFServerless\HFServerless;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\TestCase;
use Spatie\Async\Pool;

class HFServerlessTest extends TestCase
{
    private HFServerless $hfServerless;
    private MockHandler $mockHandler;

    protected function setUp(): void
    {
        $this->mockHandler = new MockHandler();
        $handlerStack = HandlerStack::create($this->mockHandler);
        $client = new Client(['handler' => $handlerStack]);

        $this->hfServerless = new HFServerless('fake_access_token');
        $this->hfServerless->setClient($client);
    }

    public function testTextGeneration(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            ['generated_text' => 'This is a generated text.']
        ])));

        $result = $this->hfServerless->textGeneration('model_id', 'input text');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('generated_text', $result[0]);
        $this->assertEquals('This is a generated text.', $result[0]['generated_text']);
    }

    public function testListModels(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            ['id' => 'model1'],
            ['id' => 'model2']
        ])));

        $result = $this->hfServerless->listModels();

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertEquals('model1', $result[0]['id']);
        $this->assertEquals('model2', $result[1]['id']);
    }

    public function testAutomaticSpeechRecognition(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'text' => 'This is a transcribed text.'
        ])));

        $result = $this->hfServerless->automaticSpeechRecognition('model_id', 'path/to/audio.mp3');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('text', $result);
        $this->assertEquals('This is a transcribed text.', $result['text']);
    }

    public function testChatCompletion(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'choices' => [
                [
                    'message' => [
                        'content' => 'This is a chat response.'
                    ]
                ]
            ]
        ])));

        $result = $this->hfServerless->chatCompletion('model_id', [
            ['role' => 'user', 'content' => 'Hello']
        ]);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('choices', $result);
        $this->assertArrayHasKey('message', $result['choices'][0]);
        $this->assertArrayHasKey('content', $result['choices'][0]['message']);
        $this->assertEquals('This is a chat response.', $result['choices'][0]['message']['content']);
    }

    public function testFeatureExtraction(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            [0.1, 0.2, 0.3]
        ])));

        $result = $this->hfServerless->featureExtraction('model_id', 'input text');

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertCount(3, $result[0]);
        $this->assertEquals([0.1, 0.2, 0.3], $result[0]);
    }

    public function testImageClassification(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            ['label' => 'cat', 'score' => 0.9],
            ['label' => 'dog', 'score' => 0.1]
        ])));

        $result = $this->hfServerless->imageClassification('model_id', 'path/to/image.jpg');

        $this->assertIsArray($result);
        $this->assertCount(2, $result);
        $this->assertEquals('cat', $result[0]['label']);
        $this->assertEquals(0.9, $result[0]['score']);
    }

    public function testImageToImage(): void
    {
        $this->mockHandler->append(new Response(200, [], 'image_data'));

        $result = $this->hfServerless->imageToImage('model_id', 'path/to/image.jpg');

        $this->assertIsString($result);
        $this->assertEquals('image_data', $result);
    }

    public function testObjectDetection(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            [
                'label' => 'person',
                'score' => 0.95,
                'box' => ['xmin' => 10, 'ymin' => 20, 'xmax' => 100, 'ymax' => 200]
            ]
        ])));

        $result = $this->hfServerless->objectDetection('model_id', 'path/to/image.jpg');

        $this->assertIsArray($result);
        $this->assertCount(1, $result);
        $this->assertEquals('person', $result[0]['label']);
        $this->assertEquals(0.95, $result[0]['score']);
        $this->assertArrayHasKey('box', $result[0]);
    }

    public function testQuestionAnswering(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'answer' => 'Paris',
            'score' => 0.98,
            'start' => 10,
            'end' => 15
        ])));

        $result = $this->hfServerless->questionAnswering('model_id', 'What is the capital of France?', 'France is a country in Europe. Its capital is Paris.');

        $this->assertIsArray($result);
        $this->assertEquals('Paris', $result['answer']);
        $this->assertEquals(0.98, $result['score']);
    }

    public function testSummarization(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            ['summary_text' => 'This is a summary.']
        ])));

        $result = $this->hfServerless->summarization('model_id', 'This is a long text that needs to be summarized.');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('summary_text', $result[0]);
        $this->assertEquals('This is a summary.', $result[0]['summary_text']);
    }

    public function testTextToImage(): void
    {
        $this->mockHandler->append(new Response(200, [], 'image_data'));

        $result = $this->hfServerless->textToImage('model_id', 'A cat sitting on a couch');

        $this->assertIsString($result);
        $this->assertEquals('image_data', $result);
    }

    public function testAsyncTextGeneration(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            ['generated_text' => 'This is an asynchronously generated text.']
        ])));

        $task = $this->hfServerless->asyncTextGeneration('model_id', 'input text');

        $result = null;
        $task->then(function ($output) use (&$result) {
            $result = $output;
        })->catch(function (\Exception $exception) {
            $this->fail($exception->getMessage());
        });

        Pool::create()->wait();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('generated_text', $result[0]);
        $this->assertEquals('This is an asynchronously generated text.', $result[0]['generated_text']);
    }

    public function testAsyncChatCompletion(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'choices' => [
                [
                    'message' => [
                        'content' => 'This is an asynchronous chat response.'
                    ]
                ]
            ]
        ])));

        $task = $this->hfServerless->asyncChatCompletion('model_id', [
            ['role' => 'user', 'content' => 'Hello']
        ]);

        $result = null;
        $task->then(function ($output) use (&$result) {
            $result = $output;
        })->catch(function (\Exception $exception) {
            $this->fail($exception->getMessage());
        });

        Pool::create()->wait();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('choices', $result);
        $this->assertArrayHasKey('message', $result['choices'][0]);
        $this->assertArrayHasKey('content', $result['choices'][0]['message']);
        $this->assertEquals('This is an asynchronous chat response.', $result['choices'][0]['message']['content']);
    }

    public function testErrorHandling(): void
    {
        $this->mockHandler->append(new RequestException('Error Communicating with Server', new Request('GET', 'test')));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to make API request: Error Communicating with Server');

        $this->hfServerless->textGeneration('model_id', 'input text');
    }

    public function testChatCompletionWithToolCalling(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'choices' => [
                [
                    'message' => [
                        'role' => 'assistant',
                        'content' => null,
                        'tool_calls' => [
                            [
                                'id' => 'call_abc123',
                                'type' => 'function',
                                'function' => [
                                    'name' => 'get_weather',
                                    'arguments' => '{"location": "New York", "unit": "celsius"}'
                                ]
                            ]
                        ]
                    ],
                    'finish_reason' => 'tool_calls'
                ]
            ]
        ])));

        $tools = [
            [
                'type' => 'function',
                'function' => [
                    'name' => 'get_weather',
                    'description' => 'Get the current weather in a given location',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'location' => [
                                'type' => 'string',
                                'description' => 'The city and state, e.g. San Francisco, CA',
                            ],
                            'unit' => ['type' => 'string', 'enum' => ['celsius', 'fahrenheit']],
                        ],
                        'required' => ['location'],
                    ],
                ],
            ],
        ];

        $result = $this->hfServerless->chatCompletion('model_id', [
            ['role' => 'user', 'content' => 'What\'s the weather like in New York?']
        ], [], true, false, false, $tools);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('choices', $result);
        $this->assertArrayHasKey('message', $result['choices'][0]);
        $this->assertArrayHasKey('tool_calls', $result['choices'][0]['message']);
        $this->assertEquals('get_weather', $result['choices'][0]['message']['tool_calls'][0]['function']['name']);
        $this->assertEquals('{"location": "New York", "unit": "celsius"}', $result['choices'][0]['message']['tool_calls'][0]['function']['arguments']);
    }
}
