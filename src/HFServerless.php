<?php

declare(strict_types=1);

namespace App\HFServerless;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Generator;
use Spatie\Async\Pool;

class HFServerless
{
    private string $apiUrl = 'https://api-inference.huggingface.co/models/';
    private string $accessToken;
    private Client $client;

    public function __construct(string $accessToken)
    {
        $this->accessToken = $accessToken;
        $this->client = new Client();
    }

    public function setClient(Client $client): void
    {
        $this->client = $client;
    }

    public function textGeneration(string $modelId, string $inputs, array $parameters = [], bool $useCache = true, bool $waitForModel = false): array
    {
        $url = $this->apiUrl . $modelId;
        $data = [
            'inputs' => $inputs,
            'parameters' => $parameters,
        ];

        $headers = $this->prepareHeaders($useCache, $waitForModel);

        try {
            $response = $this->makeRequest('POST', $url, $headers, $data);
        } catch (GuzzleException $e) {
            if ($e->getCode() === 503 && !$waitForModel) {
                return $this->textGeneration($modelId, $inputs, $parameters, $useCache, true);
            }
            throw new \RuntimeException('Failed to make API request: ' . $e->getMessage(), $e->getCode(), $e);
        }

        return $response;
    }

    public function asyncTextGeneration(string $modelId, string $inputs, array $parameters = [], bool $useCache = true, bool $waitForModel = false): \Spatie\Async\Task
    {
        return Pool::create()->add(function () use ($modelId, $inputs, $parameters, $useCache, $waitForModel) {
            return $this->textGeneration($modelId, $inputs, $parameters, $useCache, $waitForModel);
        });
    }

    public function listModels(string $search = '', int $limit = 20): array
    {
        $url = 'https://huggingface.co/api/models';
        $queryParams = [
            'search' => $search,
            'limit' => $limit,
        ];

        $headers = [
            'Authorization' => 'Bearer ' . $this->accessToken,
        ];

        try {
            return $this->makeRequest('GET', $url, $headers, [], $queryParams);
        } catch (GuzzleException $e) {
            throw new \RuntimeException('Failed to retrieve model list: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function automaticSpeechRecognition(string $modelId, string $audioFilePath, array $parameters = [], bool $useCache = true, bool $waitForModel = false): array
    {
        $url = $this->apiUrl . $modelId;

        $headers = $this->prepareHeaders($useCache, $waitForModel);

        $multipart = [
            [
                'name' => 'audio',
                'contents' => fopen($audioFilePath, 'r'),
            ],
        ];

        if (!empty($parameters)) {
            $multipart[] = [
                'name' => 'parameters',
                'contents' => json_encode($parameters),
            ];
        }

        try {
            return $this->makeMultipartRequest('POST', $url, $headers, $multipart);
        } catch (GuzzleException $e) {
            if ($e->getCode() === 503 && !$waitForModel) {
                return $this->automaticSpeechRecognition($modelId, $audioFilePath, $parameters, $useCache, true);
            }
            throw new \RuntimeException('Failed to make API request: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function chatCompletion(
        string $modelId,
        array $messages,
        array $parameters = [],
        bool $useCache = true,
        bool $waitForModel = false,
        bool $stream = false,
        ?array $tools = null,
        ?string $toolChoice = null,
        ?string $toolPrompt = null
    ): array|Generator {
        $url = $this->apiUrl . $modelId;
        $data = [
            'messages' => $messages,
        ];

        $allowedParameters = [
            'max_tokens', 'temperature', 'top_p', 'frequency_penalty', 'presence_penalty',
            'stop', 'stream', 'logprobs', 'tools', 'tool_choice', 'tool_prompt', 'response_format'
        ];

        foreach ($allowedParameters as $param) {
            if (isset($parameters[$param])) {
                $data[$param] = $parameters[$param];
            }
        }

        if ($tools !== null) {
            $data['tools'] = $tools;
        }

        if ($toolChoice !== null) {
            $data['tool_choice'] = $toolChoice;
        }

        if ($toolPrompt !== null) {
            $data['tool_prompt'] = $toolPrompt;
        }

        $data['stream'] = $stream;

        $headers = $this->prepareHeaders($useCache, $waitForModel);

        try {
            if ($stream) {
                return $this->streamResponse($url, $headers, $data);
            } else {
                $response = $this->makeRequest('POST', $url, $headers, $data);
                return $this->formatChatCompletionResponse($response);
            }
        } catch (GuzzleException $e) {
            if ($e->getCode() === 503 && !$waitForModel) {
                return $this->chatCompletion($modelId, $messages, $parameters, $useCache, true, $stream, $tools, $toolChoice, $toolPrompt);
            }
            throw new \RuntimeException('Failed to make API request: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function asyncChatCompletion(
        string $modelId,
        array $messages,
        array $parameters = [],
        bool $useCache = true,
        bool $waitForModel = false,
        bool $stream = false,
        ?array $tools = null,
        ?string $toolChoice = null,
        ?string $toolPrompt = null
    ): \Spatie\Async\Task {
        return Pool::create()->add(function () use ($modelId, $messages, $parameters, $useCache, $waitForModel, $stream, $tools, $toolChoice, $toolPrompt) {
            return $this->chatCompletion($modelId, $messages, $parameters, $useCache, $waitForModel, $stream, $tools, $toolChoice, $toolPrompt);
        });
    }

    public function featureExtraction(string $modelId, string $text, array $parameters = [], bool $useCache = true, bool $waitForModel = false): array
    {
        $url = $this->apiUrl . $modelId;
        $data = [
            'inputs' => $text,
        ];

        if (!empty($parameters)) {
            $data['parameters'] = $parameters;
        }

        $headers = $this->prepareHeaders($useCache, $waitForModel);

        try {
            $response = $this->makeRequest('POST', $url, $headers, $data);
        } catch (GuzzleException $e) {
            if ($e->getCode() === 503 && !$waitForModel) {
                return $this->featureExtraction($modelId, $text, $parameters, $useCache, true);
            }
            throw new \RuntimeException('Failed to make API request: ' . $e->getMessage(), $e->getCode(), $e);
        }

        return $response;
    }

    public function imageClassification(string $modelId, string $imagePath, array $parameters = [], bool $useCache = true, bool $waitForModel = false): array
    {
        $url = $this->apiUrl . $modelId;

        $headers = $this->prepareHeaders($useCache, $waitForModel);

        $multipart = [
            [
                'name' => 'image',
                'contents' => fopen($imagePath, 'r'),
            ],
        ];

        if (!empty($parameters)) {
            $multipart[] = [
                'name' => 'parameters',
                'contents' => json_encode($parameters),
            ];
        }

        try {
            return $this->makeMultipartRequest('POST', $url, $headers, $multipart);
        } catch (GuzzleException $e) {
            if ($e->getCode() === 503 && !$waitForModel) {
                return $this->imageClassification($modelId, $imagePath, $parameters, $useCache, true);
            }
            throw new \RuntimeException('Failed to make API request: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function imageToImage(string $modelId, string $imagePath, array $parameters = [], bool $useCache = true, bool $waitForModel = false): string
    {
        $url = $this->apiUrl . $modelId;

        $headers = $this->prepareHeaders($useCache, $waitForModel);

        $multipart = [
            [
                'name' => 'image',
                'contents' => fopen($imagePath, 'r'),
            ],
        ];

        if (!empty($parameters)) {
            $multipart[] = [
                'name' => 'parameters',
                'contents' => json_encode($parameters),
            ];
        }

        try {
            $response = $this->client->request('POST', $url, [
                'headers' => $headers,
                'multipart' => $multipart,
            ]);

            return (string) $response->getBody();
        } catch (GuzzleException $e) {
            if ($e->getCode() === 503 && !$waitForModel) {
                return $this->imageToImage($modelId, $imagePath, $parameters, $useCache, true);
            }
            throw new \RuntimeException('Failed to make API request: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function objectDetection(string $modelId, string $imagePath, array $parameters = [], bool $useCache = true, bool $waitForModel = false): array
    {
        $url = $this->apiUrl . $modelId;

        $headers = $this->prepareHeaders($useCache, $waitForModel);

        $multipart = [
            [
                'name' => 'image',
                'contents' => fopen($imagePath, 'r'),
            ],
        ];

        if (!empty($parameters)) {
            $multipart[] = [
                'name' => 'parameters',
                'contents' => json_encode($parameters),
            ];
        }

        try {
            return $this->makeMultipartRequest('POST', $url, $headers, $multipart);
        } catch (GuzzleException $e) {
            if ($e->getCode() === 503 && !$waitForModel) {
                return $this->objectDetection($modelId, $imagePath, $parameters, $useCache, true);
            }
            throw new \RuntimeException('Failed to make API request: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    public function questionAnswering(string $modelId, string $question, string $context, array $parameters = [], bool $useCache = true, bool $waitForModel = false): array
    {
        $url = $this->apiUrl . $modelId;
        $data = [
            'inputs' => [
                'question' => $question,
                'context' => $context,
            ],
        ];

        if (!empty($parameters)) {
            $data['parameters'] = $parameters;
        }

        $headers = $this->prepareHeaders($useCache, $waitForModel);

        try {
            $response = $this->makeRequest('POST', $url, $headers, $data);
        } catch (GuzzleException $e) {
            if ($e->getCode() === 503 && !$waitForModel) {
                return $this->questionAnswering($modelId, $question, $context, $parameters, $useCache, true);
            }
            throw new \RuntimeException('Failed to make API request: ' . $e->getMessage(), $e->getCode(), $e);
        }

        return $response;
    }

    public function summarization(string $modelId, string $text, array $parameters = [], bool $useCache = true, bool $waitForModel = false): array
    {
        $url = $this->apiUrl . $modelId;
        $data = [
            'inputs' => $text,
        ];

        if (!empty($parameters)) {
            $data['parameters'] = $parameters;
        }

        $headers = $this->prepareHeaders($useCache, $waitForModel);

        try {
            $response = $this->makeRequest('POST', $url, $headers, $data);
        } catch (GuzzleException $e) {
            if ($e->getCode() === 503 && !$waitForModel) {
                return $this->summarization($modelId, $text, $parameters, $useCache, true);
            }
            throw new \RuntimeException('Failed to make API request: ' . $e->getMessage(), $e->getCode(), $e);
        }

        return $response;
    }

    public function textToImage(string $modelId, string $prompt, array $parameters = [], bool $useCache = true, bool $waitForModel = false): string
    {
        $url = $this->apiUrl . $modelId;
        $data = [
            'inputs' => $prompt,
        ];

        if (!empty($parameters)) {
            $data['parameters'] = $parameters;
        }

        $headers = $this->prepareHeaders($useCache, $waitForModel);

        try {
            $response = $this->client->request('POST', $url, [
                'headers' => $headers,
                'json' => $data,
            ]);

            return (string) $response->getBody();
        } catch (GuzzleException $e) {
            if ($e->getCode() === 503 && !$waitForModel) {
                return $this->textToImage($modelId, $prompt, $parameters, $useCache, true);
            }
            throw new \RuntimeException('Failed to make API request: ' . $e->getMessage(), $e->getCode(), $e);
        }
    }

    private function prepareHeaders(bool $useCache, bool $waitForModel): array
    {
        $headers = [
            'Authorization' => 'Bearer ' . $this->accessToken,
            'Content-Type' => 'application/json',
        ];

        if (!$useCache) {
            $headers['x-use-cache'] = 'false';
        }

        if ($waitForModel) {
            $headers['x-wait-for-model'] = 'true';
        }

        return $headers;
    }

    private function makeRequest(string $method, string $url, array $headers, array $data = [], array $query = []): array
    {
        $options = [
            'headers' => $headers,
            'json' => $data,
            'query' => $query,
        ];

        try {
            $response = $this->client->request($method, $url, $options);
            $body = (string) $response->getBody();
            $decodedResponse = json_decode($body, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \RuntimeException('Failed to decode API response.');
            }

            return $decodedResponse;
        } catch (GuzzleException $e) {
            throw $e;
        }
    }

    private function makeMultipartRequest(string $method, string $url, array $headers, array $multipart): array
    {
        $options = [
            'headers' => $headers,
            'multipart' => $multipart,
        ];

        try {
            $response = $this->client->request($method, $url, $options);
            $body = (string) $response->getBody();
            $decodedResponse = json_decode($body, true);

            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \RuntimeException('Failed to decode API response.');
            }

            return $decodedResponse;
        } catch (GuzzleException $e) {
            throw $e;
        }
    }

    private function streamResponse(string $url, array $headers, array $data): Generator
    {
        $client = new Client();
        $response = $client->post($url, [
            'headers' => $headers,
            'json' => $data,
            'stream' => true,
        ]);

        $body = $response->getBody();
        while (!$body->eof()) {
            $line = $body->read(1024);
            if (strpos($line, 'data: ') === 0) {
                $jsonData = json_decode(substr($line, 6), true);
                if ($jsonData !== null) {
                    yield $this->formatChatCompletionResponse($jsonData);
                }
            }
        }
    }

    private function formatChatCompletionResponse(array $response): array
    {
        $formattedResponse = [
            'id' => $response['id'] ?? '',
            'object' => $response['object'] ?? 'chat.completion',
            'created' => $response['created'] ?? time(),
            'model' => $response['model'] ?? '',
            'choices' => array_map(function ($choice) {
                $formattedChoice = [
                    'index' => $choice['index'] ?? 0,
                    'message' => [
                        'role' => $choice['message']['role'] ?? 'assistant',
                        'content' => $choice['message']['content'] ?? '',
                    ],
                    'finish_reason' => $choice['finish_reason'] ?? null,
                ];

                if (isset($choice['message']['tool_calls'])) {
                    $formattedChoice['message']['tool_calls'] = $choice['message']['tool_calls'];
                }

                return $formattedChoice;
            }, $response['choices'] ?? []),
            'usage' => $response['usage'] ?? null,
        ];

        return $formattedResponse;
    }
}
