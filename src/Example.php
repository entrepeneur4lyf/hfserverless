<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use App\HFServerless\HFServerless;
use Spatie\Async\Pool;

// Replace 'your_access_token_here' with your actual Hugging Face access token
$accessToken = 'your_access_token_here';
$hfServerless = new HFServerless($accessToken);

try {
    // Example 1: Listing available models
    $models = $hfServerless->listModels('text-generation', 5);
    foreach ($models as $model) {
        echo "- {$model['id']}\n";
    }

    // Example 2: Asynchronous Text Generation
    echo "Performing Asynchronous Text Generation:\n";
    $modelId = 'google/gemma-2-2b-it';
    $inputs = "What is the capital of France?";
    $parameters = [
        'max_new_tokens' => 50,
        'temperature' => 0.7,
        'top_k' => 50,
        'top_p' => 0.95,
        'do_sample' => true,
    ];

    $asyncTextGenerationTask = $hfServerless->asyncTextGeneration($modelId, $inputs, $parameters);

    $asyncTextGenerationTask->then(function (array $result) {
        if (isset($result[0]['generated_text'])) {
            echo "Asynchronous Text Generation result: " . $result[0]['generated_text'] . "\n";
        }
    })->catch(function (\Exception $exception) {
        echo "Asynchronous Text Generation error: " . $exception->getMessage() . "\n";
    });

    // Example 3: Asynchronous Chat Completion
    echo "Performing Asynchronous Chat Completion:\n";
    $chatModelId = 'google/gemma-2-2b-it';
    $messages = [
        ['role' => 'user', 'content' => 'What is the capital of France?']
    ];
    $chatParameters = [
        'max_tokens' => 500,
        'temperature' => 0.7
    ];

    $asyncChatCompletionTask = $hfServerless->asyncChatCompletion($chatModelId, $messages, $chatParameters);

    $asyncChatCompletionTask->then(function ($result) {
        if (is_array($result) && isset($result['choices'][0]['message']['content'])) {
            echo "Asynchronous Chat Completion result: " . $result['choices'][0]['message']['content'] . "\n";
        }
    })->catch(function (\Exception $exception) {
        echo "Asynchronous Chat Completion error: " . $exception->getMessage() . "\n";
    });

    // Wait for all async tasks to complete
    Pool::create()->wait();

    // Example 4: Automatic Speech Recognition (synchronous)
    echo "Performing Automatic Speech Recognition:\n";
    $asrModelId = 'openai/whisper-large-v3';
    $audioFilePath = 'path/to/your/audio/file.mp3';
    $asrParameters = ['return_timestamps' => true];
    $asrResult = $hfServerless->automaticSpeechRecognition($asrModelId, $audioFilePath, $asrParameters);
    echo "Transcribed text: " . ($asrResult['text'] ?? 'No text transcribed') . "\n";

    // Example 5: Feature Extraction (synchronous)
    echo "Performing Feature Extraction:\n";
    $featureExtractionModelId = 'thenlper/gte-large';
    $text = "Today is a sunny day and I will get some ice cream.";
    $featureExtractionParameters = ['normalize' => true];
    $featureExtractionResult = $hfServerless->featureExtraction($featureExtractionModelId, $text, $featureExtractionParameters);
    if (!empty($featureExtractionResult) && !empty($featureExtractionResult[0])) {
        echo "Feature vector (first 5 elements): " . implode(', ', array_slice($featureExtractionResult[0], 0, 5)) . "...\n";
        echo "Vector dimension: " . count($featureExtractionResult[0]) . "\n";
    } else {
        echo "No feature vector extracted.\n";
    }

    // Example 6: Image Classification with wait for model
    echo "Performing Image Classification with wait for model:\n";
    $imageClassificationModelId = 'google/vit-base-patch16-224';
    $imagePath = __DIR__ . '/../tests/resources/sample_image.jpg'; // Make sure to add a sample image file
    $imageClassificationParameters = ['top_k' => 3];

    $imageClassificationResult = $hfServerless->imageClassification($imageClassificationModelId, $imagePath, $imageClassificationParameters, true, true);

    echo "Image classification results:\n";
    foreach ($imageClassificationResult as $result) {
        echo "- Label: " . ($result['label'] ?? 'Unknown') . ", Score: " . ($result['score'] ?? 'N/A') . "\n";
    }
    echo "\n";

    // Example 7: Image to Image
    echo "Performing Image to Image transformation:\n";
    $imageToImageModelId = 'timbrooks/instruct-pix2pix';
    $imagePath = __DIR__ . '/../tests/resources/sample_image.jpg'; // Make sure to add a sample image file
    $imageToImageParameters = [
        'prompt' => 'Convert the image to a watercolor painting',
        'negative_prompt' => 'low quality, blurry',
        'guidance_scale' => 7.5,
        'num_inference_steps' => 50,
    ];

    $imageToImageResult = $hfServerless->imageToImage($imageToImageModelId, $imagePath, $imageToImageParameters);

    // Save the result image
    $outputImagePath = __DIR__ . '/../tests/resources/output_image.jpg';
    file_put_contents($outputImagePath, $imageToImageResult);

    echo "Image to Image transformation completed. Output saved to: $outputImagePath\n\n";

    // Example 8: Object Detection
    echo "Performing Object Detection:\n";
    $objectDetectionModelId = 'facebook/detr-resnet-50';
    $imagePath = __DIR__ . '/../tests/resources/sample_image.jpg'; // Make sure to add a sample image file
    $objectDetectionParameters = ['threshold' => 0.9];

    $objectDetectionResult = $hfServerless->objectDetection($objectDetectionModelId, $imagePath, $objectDetectionParameters);

    echo "Object detection results:\n";
    foreach ($objectDetectionResult as $result) {
        echo "- Label: " . ($result['label'] ?? 'Unknown') . ", Score: " . ($result['score'] ?? 'N/A') . ", ";
        echo "Box: (x1: " . ($result['box']['xmin'] ?? 'N/A') . ", y1: " . ($result['box']['ymin'] ?? 'N/A') . ", ";
        echo "x2: " . ($result['box']['xmax'] ?? 'N/A') . ", y2: " . ($result['box']['ymax'] ?? 'N/A') . ")\n";
    }
    echo "\n";

    // Example 9: Question Answering
    echo "Performing Question Answering:\n";
    $questionAnsweringModelId = 'deepset/roberta-base-squad2';
    $question = "What is my name?";
    $context = "My name is Clara and I live in Berkeley.";
    $questionAnsweringParameters = ['top_k' => 1];

    $questionAnsweringResult = $hfServerless->questionAnswering($questionAnsweringModelId, $question, $context, $questionAnsweringParameters);

    echo "Question: $question\n";
    echo "Context: $context\n";
    echo "Answer: " . ($questionAnsweringResult['answer'] ?? 'No answer found') . "\n";
    echo "Score: " . ($questionAnsweringResult['score'] ?? 'N/A') . "\n";
    echo "Start: " . ($questionAnsweringResult['start'] ?? 'N/A') . "\n";
    echo "End: " . ($questionAnsweringResult['end'] ?? 'N/A') . "\n\n";

    // Example 10: Summarization with cache disabled and wait for model
    echo "Performing Summarization with cache disabled and wait for model:\n";
    $summarizationModelId = 'facebook/bart-large-cnn';
    $textToSummarize = "The tower is 324 metres (1,063 ft) tall, about the same height as an 81-storey building, and the tallest structure in Paris. Its base is square, measuring 125 metres (410 ft) on each side. During its construction, the Eiffel Tower surpassed the Washington Monument to become the tallest man-made structure in the world, a title it held for 41 years until the Chrysler Building in New York City was finished in 1930. It was the first structure to reach a height of 300 metres. Due to the addition of a broadcasting aerial at the top of the tower in 1957, it is now taller than the Chrysler Building by 5.2 metres (17 ft). Excluding transmitters, the Eiffel Tower is the second tallest free-standing structure in France after the Millau Viaduct.";
    $summarizationParameters = [
        'max_length' => 100,
        'min_length' => 30,
        'do_sample' => false
    ];

    $summarizationResult = $hfServerless->summarization($summarizationModelId, $textToSummarize, $summarizationParameters, false, true);

    echo "Original text:\n$textToSummarize\n\n";
    echo "Summarized text:\n" . ($summarizationResult[0]['summary_text'] ?? 'No summary generated') . "\n\n";

    // Example 11: Text to Image
    echo "Performing Text to Image generation:\n";
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

    // Save the generated image
    $outputImagePath = __DIR__ . '/../tests/resources/generated_image.jpg';
    file_put_contents($outputImagePath, $imageBytes);

    echo "Text to Image generation completed. Output saved to: $outputImagePath\n";
    echo "Prompt used: $prompt\n";

    // Example 12: Chat Completion with Tool Calling
    echo "Performing Chat Completion with Tool Calling:\n";
    $chatModelId = 'google/gemma-2-2b-it';
    $messages = [
        ['role' => 'user', 'content' => 'What\'s the weather like in New York?']
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
    $chatParameters = [
        'max_tokens' => 500,
        'temperature' => 0.7
    ];

    $chatResult = $hfServerless->chatCompletion(
        $chatModelId,
        $messages,
        $chatParameters,
        true,
        false,
        false,
        $tools,
        null,
        $toolPrompt
    );

    if (is_array($chatResult) && isset($chatResult['choices'][0]['message']['tool_calls'])) {
        foreach ($chatResult['choices'][0]['message']['tool_calls'] as $toolCall) {
            if (is_array($toolCall) && isset($toolCall['function']['name'], $toolCall['function']['arguments'])) {
                $functionName = $toolCall['function']['name'];
                $arguments = json_decode($toolCall['function']['arguments'], true);

                echo "Function called: $functionName\n";
                echo "Arguments: " . json_encode($arguments) . "\n";

                $toolResult = "The weather in " . ($arguments['location'] ?? 'Unknown location') . " is sunny and 75°F.";

                $messages[] = [
                    'role' => 'function',
                    'name' => $functionName,
                    'content' => $toolResult,
                ];
            }
        }

        $finalResult = $hfServerless->chatCompletion($chatModelId, $messages, $chatParameters);
        if (is_array($finalResult) && isset($finalResult['choices'][0]['message']['content'])) {
            echo "Final response: " . $finalResult['choices'][0]['message']['content'] . "\n";
        }
    } elseif (is_array($chatResult) && isset($chatResult['choices'][0]['message']['content'])) {
        echo "Chat response: " . $chatResult['choices'][0]['message']['content'] . "\n";
    }

    // Example 13: Streaming Chat Completion
    echo "Performing Streaming Chat Completion:\n";
    $streamingChatModelId = 'google/gemma-2-2b-it';
    $streamingMessages = [
        ['role' => 'user', 'content' => 'Tell me a short story about a brave knight.']
    ];
    $streamingChatParameters = [
        'max_tokens' => 200,
        'temperature' => 0.7
    ];

    $streamingChatResult = $hfServerless->chatCompletion(
        $streamingChatModelId,
        $streamingMessages,
        $streamingChatParameters,
        true,
        false,
        true // Set streaming to true
    );

    echo "Streaming response:\n";
    $fullResponse = '';
    foreach ($streamingChatResult as $chunk) {
        if (isset($chunk['choices'][0]['message']['content'])) {
            $content = $chunk['choices'][0]['message']['content'];
            echo $content;
            $fullResponse .= $content;
            flush(); // Ensure the output is immediately displayed
        }
    }
    echo "\n\nFull response:\n$fullResponse\n";

} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
