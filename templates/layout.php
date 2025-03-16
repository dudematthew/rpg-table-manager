<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $this->e($title) ?> - RPG Table Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <script>
        // Check if we're in an iframe
        const isInIframe = window.self !== window.top;
        if (isInIframe) {
            document.documentElement.classList.add('in-iframe');
        }
    </script>
    <style>
        html, body {
            height: 100%;
        }
        
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            overflow-x: hidden;
        }
        
        main {
            flex: 1;
        }
        
        .in-iframe .hide-in-iframe {
            display: none !important;
        }
        
        /* Custom styling for dice tables */
        .dice-table th {
            position: sticky;
            top: 0;
            background: white;
            z-index: 10;
        }
        
        .dice-row:nth-child(even) {
            background-color: #f9fafb;
        }
        
        .dice-row:hover {
            background-color: #f3f4f6;
        }

        /* Input styling */
        .form-input {
            @apply shadow-sm border-gray-300 focus:border-indigo-500 rounded-md focus:ring-indigo-500;
            transition: all 0.2s;
        }
        
        .form-input.has-error {
            @apply border-red-500 focus:border-red-500 focus:ring-red-500;
        }

        .error-message {
            @apply mt-1 text-red-600 text-sm;
        }

        /* Tooltip styling */
        .tooltip {
            @apply invisible z-50 absolute bg-gray-900 shadow-lg px-3 py-2 rounded text-white text-sm;
            max-width: 200px;
        }

        .tooltip-trigger:hover .tooltip {
            @apply visible;
        }

        /* Responsive container */
        @media (max-width: 640px) {
            .container {
                padding-left: 1rem;
                padding-right: 1rem;
            }
        }
    </style>
</head>
<body class="bg-gray-50">
    <header class="bg-indigo-600 shadow-lg text-white hide-in-iframe">
        <nav class="mx-auto px-4 sm:px-6 lg:px-8 py-4 container">
            <div class="flex sm:flex-row flex-col justify-between items-center space-y-4 sm:space-y-0">
                <a href="<?= $basePath ?>/" class="font-bold text-xl sm:text-2xl">RPG Table Manager</a>
                <div class="flex space-x-4">
                    <a href="<?= $basePath ?>/" class="hover:text-indigo-200 transition-colors duration-200">Projects</a>
                </div>
            </div>
        </nav>
    </header>

    <main class="mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-8 container">
        <div class="mx-auto w-full max-w-7xl">
            <?= $this->section('content') ?>
        </div>
    </main>

    <footer class="bg-gray-800 mt-auto py-4 text-white hide-in-iframe">
        <div class="mx-auto px-4 sm:px-6 lg:px-8 text-center container">
            <p>&copy; <?= date('Y') ?> RPG Table Manager</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/alpinejs@2.8.2/dist/alpine.min.js" defer></script>
    <?= $this->section('scripts') ?>
</body>
</html> 