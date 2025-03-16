<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->e($table->name) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <style>
        html, body {
            margin: 0;
            padding: 0;
            height: 100%;
            background: transparent;
        }
        
        .dice-row:nth-child(even) {
            background-color: #f9fafb;
        }
        
        .dice-row:hover {
            background-color: #f3f4f6;
        }
    </style>
</head>
<body>
    <div class="p-4">
        <div class="bg-white shadow rounded-lg overflow-hidden">
            <div class="p-4 border-gray-200 border-b">
                <h2 class="font-semibold text-gray-900 text-lg"><?= $this->e($table->name) ?></h2>
                <div class="mt-1 font-medium text-indigo-600 text-sm">
                    <?= $this->e($table->dice_expression) ?>
                </div>
                <?php if ($table->description): ?>
                    <p class="mt-2 text-gray-600 text-sm"><?= $this->e($table->description) ?></p>
                <?php endif ?>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gray-50 border-gray-200 border-b">
                            <th class="px-4 py-2 font-medium text-gray-500 text-sm text-left">Range</th>
                            <th class="px-4 py-2 font-medium text-gray-500 text-sm text-left">Result</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($table->entries as $entry): ?>
                            <tr class="border-gray-100 border-b dice-row">
                                <td class="px-4 py-2 text-gray-900 text-sm">
                                    <?php if ($entry->min_value === $entry->max_value): ?>
                                        <?= $this->e($entry->min_value) ?>
                                    <?php else: ?>
                                        <?= $this->e($entry->min_value) ?>-<?= $this->e($entry->max_value) ?>
                                    <?php endif ?>
                                </td>
                                <td class="px-4 py-2 text-gray-900 text-sm"><?= $this->e($entry->result) ?></td>
                            </tr>
                        <?php endforeach ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html> 