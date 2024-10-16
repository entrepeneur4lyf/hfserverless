# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Changed
- Refactored `asyncTextGeneration` and `asyncChatCompletion` methods in HFServerless class to use Spatie/Async correctly.
- Updated these methods to run tasks and return results directly, rather than returning Task objects.
- Modified src/Example.php to demonstrate the correct usage of the updated async methods.

### Improved
- Enhanced asynchronous operation handling for better performance and reliability.
- Simplified usage of async methods in client code.

### Added
- Tool calling support for chat completion methods.
- New parameters in `chatCompletion` and `asyncChatCompletion` methods: `$tools`, `$toolChoice`, and `$toolPrompt`.
- Asynchronous support for text generation and chat completion methods.
- New methods: `asyncTextGeneration` and `asyncChatCompletion`.
- Enhanced test suite in HFServerlessTest.php to cover new functionalities, including tool calling and asynchronous operations.
- Updated README.md with examples of tool calling usage and asynchronous methods.
- New example in src/Example.php demonstrating tool calling functionality.

### Changed
- Updated existing synchronous methods to include cache control and model loading options.
- Improved error handling and response formatting for chat completion methods.

### Improved
- Code organization and documentation for better readability and maintainability.
- Performance optimizations for large language model interactions.

## [0.0.1] - 2024-10-16

### Added
- Initial release of the HFServerless package.
- Support for various Hugging Face models and API endpoints.
- Synchronous methods for:
  - Text generation
  - Chat completion
  - Automatic speech recognition
  - Feature extraction
  - Image classification
  - Image-to-image transformation
  - Object detection
  - Question answering
  - Summarization
  - Text-to-image generation
- Cache control and model loading options for all API requests.
- Comprehensive test suite for all public methods.

[Unreleased]: https://github.com/entrepeneur4lyf/hfserverless/compare/v0.0.1...HEAD
[0.0.1]: https://github.com/entrepeneur4lyf/hfserverless/releases/tag/v0.0.1
