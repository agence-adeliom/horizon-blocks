# Horizon Blocks

Horizon Blocks is a versatile library designed to integrate seamlessly with Horizon Tools, enabling you to create and reuse blocks across multiple sites with ease.

## Key Features

- **Automatic Block Management**: Effortlessly list and import blocks into your project using the `import:block` command.
- **Simplified Block Creation**: Easily define new base blocks by adding class and template files.

## Installation Instructions

To set up Horizon Blocks in your project, follow these steps:

1. **Configure Composer Repository**:

   Update your `composer.json` file to include Horizon Blocks as a repository. Make sure to add the appropriate repository configuration under the `"repositories"` section.

```json
{
  "type": "vcs",
  "url": "git@github.com:agence-adeliom/horizon-blocks.git"
}
```

2. **Install the Package**:

```bash
composer require agence-adeliom/horizon-blocks
```

## How to Use

- **Import Existing Blocks**: Use the `import:block` command to view and import blocks available for your project.

## Example Setup

Here’s a quick guide to setting up a new block:

1. **Define a Block Class**:
    - Path: `src/Blocks/MyCustomBlock.php`
    - Content:
      ```php
      <?php
 
      namespace App\Blocks;
 
      class MyCustomBlock
      {
          // Define block logic here
      }
      ```

2. **Create a Block Template**:
    - Path: `resources/views/blocks/my-custom-block.blade.php`
    - Content:
      ```html
      <div>
          <!-- Block content goes here -->
      </div>
      ```
