# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.1] - 2025-01-XX

### Added
- Enhanced API key management system with secure storage in WordPress options
- New Settings page for configuring Gemini API key
- Comprehensive error logging system with structured log entries
- Unit tests for Gemini service functionality
- Improved error handling with user-friendly messages
- TypeScript configuration for Node.js build process

### Changed
- Updated Vite configuration to properly handle WordPress dependencies
- Enhanced Gemini service with better error handling and validation
- Improved API responses with internationalized error messages
- Updated README with detailed configuration instructions

### Fixed
- Missing `tsconfig.node.json` file causing build issues
- WordPress React dependency handling in Vite builds
- API key validation and storage security
- Error handling in API endpoints

### Security
- API keys are now sanitized before storage
- Enhanced permission checks for API endpoints
- Secure API key masking in admin interface
- Proper nonce verification for all admin actions

## [1.0.0] - 2025-07-11

### Added
- Initial release
- Core SEO and performance scanning functionality
- Admin dashboard and editor integration
- Gemini AI integration for meta tag suggestions
- Manual full site scan capability
- Core Web Vitals monitoring 