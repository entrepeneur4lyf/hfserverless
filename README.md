# HFServerless - Hugging Face Serverless Inference API PHP Wrapper

The Serverless Inference API offers a fast and free way to explore thousands of models for a variety of tasks. Whether you're prototyping a new application or experimenting with ML capabilities, this API gives you instant access to high-performing models across multiple domains:

- Text Generation: Including large language models and tool-calling prompts, generate and experiment with high-quality responses.
- Image Generation: Easily create customized images, including LoRAs for your own styles.
- Document Embeddings: Build search and retrieval systems with SOTA embeddings.
- Classical AI Tasks: Ready-to-use models for text classification, image classification, speech recognition, and more.

⚡ Fast and Free to Get Started: The Inference API is free with higher rate limits for PRO users.

![hfserverless](https://github.com/user-attachments/assets/70af9793-a776-4406-8eba-8b47ad6587c3)

This package provides a simple PHP wrapper for the Hugging Face Serverless Inference API, allowing you to easily integrate Hugging Face's powerful machine learning models into your PHP projects.

## Requirements

- PHP 8.0 or higher
- Composer

## Installation

You can install this package via Composer:

```bash
composer require entrepeneur4lyf/hfserverless
```

This will install the package and its dependencies, including Guzzle and Spatie/Async, which are used for making HTTP requests and handling asynchronous operations.

## Usage

Here's a basic example of how to use the HFServerless package:

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\HFServerless\HFServerless;

// Replace 'your_access_token_here' with your actual Hugging Face access token
$accessToken = 'your_access_token_here';
$hfServerless = new HFServerless($accessToken);

try {
    // Example 1: Listing available models
    $models = $hfServerless->listModels('text-generation', 5);
    foreach ($models as $model) {
        echo "- {$model['id']}\n";
    }

    // Example 2: Text Generation
    $modelId = 'google/gemma-2-2b-it';
    $inputs = "Can you please let us know more details about your ";
    $parameters = [
        'max_new_tokens' => 50,
        'temperature' => 0.7,
        'top_k' => 50,
        'top_p' => 0.95,
        'do_sample' => true,
    ];
    $result = $hfServerless->textGeneration($modelId, $inputs, $parameters);
    echo "Generated text: " . $result[0]['generated_text'] . "\n";

    // Example 3: Asynchronous Text Generation
    echo "Performing Asynchronous Text Generation:\n";
    $asyncTextGenerationResult = $hfServerless->asyncTextGeneration($modelId, $inputs, $parameters);
    echo "Asynchronous Text Generation result: " . $asyncTextGenerationResult[0]['generated_text'] . "\n";

    // Example 4: Chat Completion
    $chatModelId = 'google/gemma-2-2b-it';
    $messages = [
        ['role' => 'user', 'content' => 'What is the capital of France?']
    ];
    $chatParameters = [
        'max_tokens' => 500,
        'temperature' => 0.7
    ];
    $chatResult = $hfServerless->chatCompletion($chatModelId, $messages, $chatParameters);
    echo "Chat response: " . $chatResult['choices'][0]['message']['content'] . "\n";

    // Example 5: Asynchronous Chat Completion
    echo "Performing Asynchronous Chat Completion:\n";
    $asyncChatCompletionResult = $hfServerless->asyncChatCompletion($chatModelId, $messages, $chatParameters);
    echo "Asynchronous Chat Completion result: " . $asyncChatCompletionResult['choices'][0]['message']['content'] . "\n";

    // Example 6: Automatic Speech Recognition
    $asrModelId = 'openai/whisper-large-v3';
    $audioFilePath = 'path/to/your/audio/file.mp3';
    $asrParameters = ['return_timestamps' => true];
    $asrResult = $hfServerless->automaticSpeechRecognition($asrModelId, $audioFilePath, $asrParameters);
    echo "Transcribed text: " . $asrResult['text'] . "\n";

    // Example 7: Feature Extraction
    $featureExtractionModelId = 'thenlper/gte-large';
    $text = "Today is a sunny day and I will get some ice cream.";
    $featureExtractionParameters = ['normalize' => true];
    $featureExtractionResult = $hfServerless->featureExtraction($featureExtractionModelId, $text, $featureExtractionParameters);
    echo "Feature vector (first 5 elements): " . implode(', ', array_slice($featureExtractionResult[0], 0, 5)) . "...\n";
    echo "Vector dimension: " . count($featureExtractionResult[0]) . "\n";

    // Example 8: Image Classification
    $imageClassificationModelId = 'google/vit-base-patch16-224';
    $imagePath = 'path/to/your/image.jpg';
    $imageClassificationParameters = ['top_k' => 3];
    $imageClassificationResult = $hfServerless->imageClassification($imageClassificationModelId, $imagePath, $imageClassificationParameters);
    foreach ($imageClassificationResult as $result) {
        echo "Label: {$result['label']}, Score: {$result['score']}\n";
    }

    // Example 9: Image to Image
    $imageToImageModelId = 'timbrooks/instruct-pix2pix';
    $imagePath = 'path/to/your/input_image.jpg';
    $imageToImageParameters = [
        'prompt' => 'Convert the image to a watercolor painting',
        'negative_prompt' => 'low quality, blurry',
        'guidance_scale' => 7.5,
        'num_inference_steps' => 50,
    ];
    $imageToImageResult = $hfServerless->imageToImage($imageToImageModelId, $imagePath, $imageToImageParameters);
    file_put_contents('path/to/your/output_image.jpg', $imageToImageResult);
    echo "Image to Image transformation completed. Output saved.\n";

    // Example 10: Object Detection
    $objectDetectionModelId = 'facebook/detr-resnet-50';
    $imagePath = 'path/to/your/image.jpg';
    $objectDetectionParameters = ['threshold' => 0.9];
    $objectDetectionResult = $hfServerless->objectDetection($objectDetectionModelId, $imagePath, $objectDetectionParameters);
    foreach ($objectDetectionResult as $result) {
        echo "Label: {$result['label']}, Score: {$result['score']}, ";
        echo "Box: (x1: {$result['box']['xmin']}, y1: {$result['box']['ymin']}, ";
        echo "x2: {$result['box']['xmax']}, y2: {$result['box']['ymax']})\n";
    }

    // Example 11: Question Answering
    $questionAnsweringModelId = 'deepset/roberta-base-squad2';
    $question = "What is my name?";
    $context = "My name is Clara and I live in Berkeley.";
    $questionAnsweringParameters = ['top_k' => 1];
    $questionAnsweringResult = $hfServerless->questionAnswering($questionAnsweringModelId, $question, $context, $questionAnsweringParameters);
    echo "Question: $question\n";
    echo "Context: $context\n";
    echo "Answer: {$questionAnsweringResult['answer']}\n";
    echo "Score: {$questionAnsweringResult['score']}\n";

    // Example 12: Summarization
    $summarizationModelId = 'facebook/bart-large-cnn';
    $textToSummarize = "The tower is 324 metres (1,063 ft) tall, about the same height as an 81-storey building, and the tallest structure in Paris. Its base is square, measuring 125 metres (410 ft) on each side. During its construction, the Eiffel Tower surpassed the Washington Monument to become the tallest man-made structure in the world, a title it held for 41 years until the Chrysler Building in New York City was finished in 1930. It was the first structure to reach a height of 300 metres. Due to the addition of a broadcasting aerial at the top of the tower in 1957, it is now taller than the Chrysler Building by 5.2 metres (17 ft). Excluding transmitters, the Eiffel Tower is the second tallest free-standing structure in France after the Millau Viaduct.";
    $summarizationParameters = [
        'max_length' => 100,
        'min_length' => 30,
        'do_sample' => false
    ];
    $summarizationResult = $hfServerless->summarization($summarizationModelId, $textToSummarize, $summarizationParameters);
    echo "Summarized text: {$summarizationResult[0]['summary_text']}\n";

    // Example 13: Text to Image
    $textToImageModelId = 'black-forest-labs/FLUX.1-dev';
    $prompt = "Astronaut riding a horse";
    $textToImageParameters = [
        'negative_prompt' => 'blurry, bad quality',
        'guidance_scale' => 7.5,
        'num_inference_steps' => 50,
        'target_size' => [
            'width' => 512,
            'height' => 512
        ],
    ];
    $imageBytes = $hfServerless->textToImage($textToImageModelId, $prompt, $textToImageParameters);
    file_put_contents('generated_image.jpg', $imageBytes);
    echo "Text to Image generation completed. Output saved to: generated_image.jpg\n";

    // Example 14: Chat Completion with Tool Calling
    echo "Performing Chat Completion with Tool Calling:\n";
    $toolCallingMessages = [
        ['role' => 'user', 'content' => 'What's the weather like in New York?']
    ];
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
    $toolPrompt = "You have access to a weather function. Use it to answer questions about the weather.";
    $toolCallingResult = $hfServerless->chatCompletion($chatModelId, $toolCallingMessages, $chatParameters, true, false, false, $tools, null, $toolPrompt);
    
    if (isset($toolCallingResult['choices'][0]['message']['tool_calls'])) {
        foreach ($toolCallingResult['choices'][0]['message']['tool_calls'] as $toolCall) {
            $functionName = $toolCall['function']['name'];
            $arguments = json_decode($toolCall['function']['arguments'], true);
            echo "Function called: $functionName\n";
            echo "Arguments: " . json_encode($arguments) . "\n";
        }
    } else {
        echo "Chat response: " . $toolCallingResult['choices'][0]['message']['content'] . "\n";
    }

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
```

## Features

- Easy-to-use wrapper for the Hugging Face Serverless Inference API
- Support for various Hugging Face models
- Automatic handling of model loading and caching
- Ability to list available models
- Support for Text Generation (synchronous and asynchronous)
- Support for Automatic Speech Recognition (ASR)
- Support for Chat Completion (synchronous and asynchronous)
- Support for Feature Extraction
- Support for Image Classification
- Support for Image to Image transformation
- Support for Object Detection
- Support for Question Answering
- Support for Summarization
- Support for Text to Image generation
- Support for Tool Calling in Chat Completion
- Cache control options for all API requests
- Option to wait for model loading
- Asynchronous operations for improved performance
- Uses Guzzle for efficient and flexible HTTP requests
- Strict typing for improved code reliability

## Methods

All methods now support two additional parameters:

- `$useCache` (default: true): When set to false, it disables the cache for the specific request, ensuring a new query is made to the API.
- `$waitForModel` (default: false): When set to true, it instructs the API to wait for the model to load if it's not ready, instead of returning a 503 error.

### Synchronous Methods

- `textGeneration(string $modelId, string $inputs, array $parameters = [], bool $useCache = true, bool $waitForModel = false): array`
- `listModels(string $search = '', int $limit = 20): array`
- `automaticSpeechRecognition(string $modelId, string $audioFilePath, array $parameters = [], bool $useCache = true, bool $waitForModel = false): array`
- `chatCompletion(string $modelId, array $messages, array $parameters = [], bool $useCache = true, bool $waitForModel = false, bool $stream = false, ?array $tools = null, ?string $toolChoice = null, ?string $toolPrompt = null): array|Generator`
- `featureExtraction(string $modelId, string $text, array $parameters = [], bool $useCache = true, bool $waitForModel = false): array`
- `imageClassification(string $modelId, string $imagePath, array $parameters = [], bool $useCache = true, bool $waitForModel = false): array`
- `imageToImage(string $modelId, string $imagePath, array $parameters = [], bool $useCache = true, bool $waitForModel = false): string`
- `objectDetection(string $modelId, string $imagePath, array $parameters = [], bool $useCache = true, bool $waitForModel = false): array`
- `questionAnswering(string $modelId, string $question, string $context, array $parameters = [], bool $useCache = true, bool $waitForModel = false): array`
- `summarization(string $modelId, string $text, array $parameters = [], bool $useCache = true, bool $waitForModel = false): array`
- `textToImage(string $modelId, string $prompt, array $parameters = [], bool $useCache = true, bool $waitForModel = false): string`

### Asynchronous Methods

- `asyncTextGeneration(string $modelId, string $inputs, array $parameters = [], bool $useCache = true, bool $waitForModel = false): array`
- `asyncChatCompletion(string $modelId, array $messages, array $parameters = [], bool $useCache = true, bool $waitForModel = false, bool $stream = false, ?array $tools = null, ?string $toolChoice = null, ?string $toolPrompt = null): array|Generator`

These asynchronous methods now directly return the result of the operation, similar to their synchronous counterparts. The asynchronous execution is handled internally by the HFServerless class.

## Tool Calling in Chat Completion

The `chatCompletion` and `asyncChatCompletion` methods support tool calling. You can provide a list of tools, a tool choice, and a tool prompt to enable the model to use external functions during the conversation. Here's a brief explanation of the new parameters:

- `$tools`: An array of tool definitions that the model can use.
- `$toolChoice`: Specifies which tool the model should use (optional).
- `$toolPrompt`: A prompt to be appended before the tools, providing context for their use.

## Cache Control and Model Loading

All methods support cache control and model loading options through the `$useCache` and `$waitForModel` parameters. These options allow you to:

1. Disable caching for specific requests by setting `$useCache` to `false`.
2. Wait for a model to load before processing the request by setting `$waitForModel` to `true`.

Example usage:

```php
// Perform text generation without using cache and waiting for the model to load
$result = $hfServerless->textGeneration($modelId, $inputs, $parameters, false, true);
```

These options provide more control over the API's behavior, especially useful when working with cold models or when fresh results are required.

## Testing

This package comes with a comprehensive test suite. To run the tests, follow these steps:

1. Make sure you have installed all dependencies:

```bash
composer install
```

2. Run the tests using PHPUnit:

```bash
./vendor/bin/phpunit tests
```

The test suite includes unit tests for all public methods of the HFServerless class, as well as tests for error handling, cache control, model loading, and streaming responses.

## Contributing

Please see [CONTRIBUTING.md](CONTRIBUTING.md) for details on how to contribute to this project.

## License

This project is licensed under the MIT License. See the [LICENSE.md](LICENSE.md) file for details.
