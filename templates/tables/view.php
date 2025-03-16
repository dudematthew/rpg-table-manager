<?php 
// Generate Open Graph meta tags for this table
$current_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
$og_description = $table->description ?? "Dice table for {$table->dice_expression}";

// Pass these variables to the layout
$this->layout('layout', [
    'title' => $table->name,
    'og_type' => 'article',
    'og_description' => $og_description,
    'current_url' => $current_url
]);
?>

<div x-data="tableEditor(<?= htmlspecialchars(json_encode([
    'tableId' => $table->id,
    'entries' => $table->entries->toArray(),
    'diceExpression' => $table->dice_expression
]), ENT_QUOTES, 'UTF-8') ?>)" 
    x-init="init"
    class="space-y-6">
    
    <!-- Mobile-friendly header section -->
    <div class="flex flex-col space-y-4">
        <!-- Navigation and title section -->
        <div class="flex sm:flex-row flex-col items-start sm:items-center gap-4">
            <div class="flex flex-1 items-center gap-4 min-w-0">
                <a href="<?= $basePath ?>/projects/<?= $this->e($table->project_id) ?>" 
                   class="flex-shrink-0 text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <h1 class="font-bold text-gray-900 text-xl sm:text-3xl truncate">
                    <?= $this->e($table->name) ?>
                </h1>
            </div>

            <!-- Action buttons -->
            <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto">
                <a href="<?= $basePath ?>/tables/<?= $this->e($table->id) ?>/iframe" 
                   class="inline-flex sm:flex-initial flex-1 justify-center items-center bg-white hover:bg-gray-50 shadow-sm px-4 py-2.5 border border-gray-300 rounded-md h-10 font-medium text-gray-700 text-sm">
                    <svg class="mr-2 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                    Embed
                </a>
                <a href="<?= $basePath ?>/tables/<?= $this->e($table->id) ?>/export/markdown" 
                   class="inline-flex sm:flex-initial flex-1 justify-center items-center bg-white hover:bg-gray-50 shadow-sm px-4 py-2.5 border border-gray-300 rounded-md h-10 font-medium text-gray-700 text-sm">
                    Export MD
                </a>
                <a href="<?= $basePath ?>/tables/<?= $this->e($table->id) ?>/export/csv" 
                   class="inline-flex sm:flex-initial flex-1 justify-center items-center bg-white hover:bg-gray-50 shadow-sm px-4 py-2.5 border border-gray-300 rounded-md h-10 font-medium text-gray-700 text-sm">
                    Export CSV
                </a>
            </div>
        </div>

        <!-- Dice expression and description -->
        <div class="flex sm:flex-row flex-col items-start sm:items-center gap-3">
            <div class="group relative">
                <span class="inline-flex items-center gap-2 bg-indigo-100 px-4 py-2 rounded-md font-medium text-indigo-800 text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    <?= $this->e($table->dice_expression) ?>
                    <span class="font-normal text-indigo-600 text-xs">
                        (<span x-text="minPossible"></span>-<span x-text="maxPossible"></span>)
                    </span>
                </span>
            </div>
            <?php if ($table->description): ?>
                <p class="text-gray-600 text-sm"><?= $this->e($table->description) ?></p>
            <?php endif ?>
        </div>
    </div>

    <!-- Table content section -->
    <div class="bg-white shadow rounded-lg overflow-hidden">
        <div class="p-4 sm:p-6">
            <!-- Table header -->
            <div class="flex sm:flex-row flex-col justify-between items-start sm:items-center gap-4 mb-6">
                <div class="space-y-1">
                    <h2 class="font-medium text-gray-900 text-lg">Table Entries</h2>
                    <p class="text-gray-500 text-sm">Values will be automatically adjusted to stay within the valid range.</p>
                </div>
                <div class="flex gap-2 w-full sm:w-auto">
                    <button @click="quickFill" 
                            class="inline-flex sm:flex-initial flex-1 justify-center items-center bg-gray-100 hover:bg-gray-200 px-4 py-2.5 border border-transparent rounded-md h-10 font-medium text-gray-700 text-sm transition-colors">
                        Quick Fill
                    </button>
                    <button @click="autofillAllRanges" 
                            class="inline-flex sm:flex-initial flex-1 justify-center items-center bg-indigo-50 hover:bg-indigo-100 px-4 py-2.5 border border-transparent rounded-md h-10 font-medium text-indigo-600 text-sm transition-colors">
                        <svg class="mr-1.5 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Auto-fill All
                    </button>
                    <button @click="addEntry" 
                            class="inline-flex sm:flex-initial flex-1 justify-center items-center bg-indigo-100 hover:bg-indigo-200 px-4 py-2.5 border border-transparent rounded-md h-10 font-medium text-indigo-700 text-sm transition-colors">
                        Add Entry
                    </button>
                </div>
            </div>

            <!-- Delete All button -->
            <div class="flex justify-between mb-6">
                <button @click="saveEntries" 
                        :class="pendingChanges ? 'bg-amber-50 text-amber-600' : dirtyEntries ? 'bg-blue-50 text-blue-600' : 'bg-green-50 text-green-600'"
                        class="inline-flex items-center hover:bg-green-100 px-4 py-2.5 border border-transparent rounded-md min-w-[140px] h-10 font-medium text-sm transition-colors">
                    <template x-if="pendingChanges">
                        <svg class="mr-2 -ml-1 w-4 h-4 animate-spin" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </template>
                    <template x-if="!pendingChanges && dirtyEntries">
                        <svg class="mr-2 -ml-1 w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                        </svg>
                    </template>
                    <template x-if="!pendingChanges && !dirtyEntries">
                        <svg class="mr-2 -ml-1 w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </template>
                    <span x-text="pendingChanges ? 'Saving...' : dirtyEntries ? 'Save Changes' : 'Saved'"></span>
                </button>
                <button @click="confirmDeleteAll" 
                        class="inline-flex items-center bg-red-50 hover:bg-red-100 px-4 py-2.5 border border-transparent rounded-md h-10 font-medium text-red-600 text-sm transition-colors">
                    <svg class="mr-1.5 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Clear Table
                </button>
            </div>

            <!-- Save indicator -->
            <div id="saveSuccess" class="hidden flex items-center mb-4 text-green-600 text-sm">
                <svg class="mr-2 -ml-1 w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                Changes saved successfully
            </div>

            <!-- Table -->
            <div class="-mx-4 sm:-mx-6">
                <div class="min-w-full align-middle">
                    <!-- Desktop header - hidden on mobile -->
                    <table class="hidden sm:table divide-y divide-gray-200 min-w-full">
                        <thead>
                            <tr class="bg-gray-50">
                                <th scope="col" class="pr-3 pb-3 pl-4 sm:pl-6 w-1/3 font-medium text-gray-500 text-sm text-left">Range</th>
                                <th scope="col" class="px-3 pb-3 font-medium text-gray-500 text-sm text-left">Result</th>
                                <th scope="col" class="pr-4 sm:pr-6 pb-3 pl-3 w-24 font-medium text-gray-500 text-sm text-right">Actions</th>
                            </tr>
                        </thead>
                    </table>

                    <!-- Mobile cards / Desktop rows -->
                    <div class="sm:hidden divide-y divide-gray-200">
                        <template x-for="(entry, index) in entries" :key="entry.id || index">
                            <div class="space-y-3 hover:bg-gray-50 p-6 transition-colors"
                                 draggable="true"
                                 @dragstart="startDrag(index, $event)"
                                 @dragend="endDrag($event)"
                                 @dragover="dragOver(index, $event)"
                                 @drop="drop(index, $event)">
                                <!-- Drag handle -->
                                <div class="flex justify-between items-center -mx-2 -mt-2 px-2 py-2 cursor-move">
                                    <span class="font-medium text-gray-700 text-sm">Entry #<span x-text="index + 1"></span></span>
                                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16" />
                                    </svg>
                                </div>
                                
                                <!-- Range section -->
                                <div class="space-y-3">
                                    <label class="block font-medium text-gray-500 text-sm">Range</label>
                                    <div class="flex items-center gap-3">
                                        <div class="relative flex-1">
                                            <input type="number" 
                                                x-model.number="entry.min_value" 
                                                @input="fixRange(entry)"
                                                @change="dirtyEntries = true; saveEntries()"
                                                @blur="if(entry.min_value === '') { entry.min_value = minPossible; fixRange(entry); }"
                                                class="form-input px-3 w-20 text-sm"
                                                :min="minPossible"
                                                :max="maxPossible">
                                            <span class="right-0 absolute inset-y-0 flex items-center pr-2 text-gray-400 pointer-events-none">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                                </svg>
                                            </span>
                                        </div>
                                        <span class="text-gray-500">-</span>
                                        <div class="relative flex-1">
                                            <input type="number" 
                                                x-model.number="entry.max_value" 
                                                @input="fixRange(entry)"
                                                @change="dirtyEntries = true; saveEntries()"
                                                @blur="if(entry.max_value === '') { entry.max_value = entry.min_value || minPossible; fixRange(entry); }"
                                                class="form-input px-3 w-20 text-sm"
                                                :min="minPossible"
                                                :max="maxPossible">
                                            <span class="right-0 absolute inset-y-0 flex items-center pr-2 text-gray-400 pointer-events-none">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                                </svg>
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Result section -->
                                <div class="space-y-3">
                                    <label class="block font-medium text-gray-500 text-sm">Result</label>
                                    <div class="relative">
                                        <input type="text" 
                                            x-model="entry.result" 
                                            @input="debounce(() => saveEntries())"
                                            @change="dirtyEntries = true; saveEntries()"
                                            class="form-input px-3 pr-8 w-full text-sm"
                                            placeholder="Enter result...">
                                        <div class="right-0 absolute inset-y-0 flex items-center pr-2 text-gray-400 pointer-events-none">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                            </svg>
                                        </div>
                                    </div>
                                </div>

                                <!-- Actions section -->
                                <div class="pt-3">
                                    <button @click="removeEntry(index)" 
                                            class="inline-flex justify-center items-center hover:bg-red-50 px-4 py-2.5 rounded w-full h-10 font-medium text-red-600 hover:text-red-900 text-sm transition-colors">
                                        <svg class="mr-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        Delete Entry
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>

                    <!-- Desktop view table -->
                    <table class="hidden sm:table divide-y divide-gray-200 min-w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="px-3 py-3 w-16 font-medium text-gray-500 text-xs text-left uppercase tracking-wider">
                                    Min
                                </th>
                                <th scope="col" class="px-3 py-3 w-16 font-medium text-gray-500 text-xs text-left uppercase tracking-wider">
                                    Max
                                </th>
                                <th scope="col" class="px-3 py-3 font-medium text-gray-500 text-xs text-left uppercase tracking-wider">
                                    Result
                                </th>
                                <th scope="col" class="relative px-3 py-3 w-24">
                                    <span class="sr-only">Actions</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <template x-for="(entry, index) in entries" :key="index">
                                <tr class="hover:bg-gray-50 transition-colors cursor-move entry-row"
                                    draggable="true"
                                    @dragstart="startDrag(index, $event)"
                                    @dragend="endDrag($event)"
                                    @dragover="dragOver(index, $event)"
                                    @drop="drop(index, $event)">
                                    <td class="px-3 py-4 w-16 whitespace-nowrap">
                                        <input type="number" 
                                            x-model="entry.min_value" 
                                            @input="fixRange(entry)"
                                            @change="dirtyEntries = true; saveEntries()"
                                            class="form-input w-full text-sm"
                                            :min="minPossible"
                                            :max="maxPossible">
                                    </td>
                                    <td class="px-3 py-4 w-16 whitespace-nowrap">
                                        <input type="number" 
                                            x-model="entry.max_value" 
                                            @input="fixRange(entry)"
                                            @change="dirtyEntries = true; saveEntries()"
                                            class="form-input w-full text-sm"
                                            :min="minPossible"
                                            :max="maxPossible">
                                    </td>
                                    <td class="px-3 py-4 whitespace-nowrap">
                                        <input type="text" 
                                            x-model="entry.result" 
                                            @input="debounce(() => saveEntries())"
                                            @change="dirtyEntries = true; saveEntries()"
                                            class="form-input px-3 pr-8 w-full text-sm"
                                            placeholder="Enter result...">
                                    </td>
                                    <td class="py-3 pr-4 sm:pr-6 pl-3 text-right">
                                        <button @click="removeEntry(index)" 
                                                class="inline-flex justify-center items-center hover:bg-red-50 px-4 py-2.5 border border-transparent rounded-md h-10 font-medium text-red-600 hover:text-red-900 text-sm transition-colors">
                                            <svg class="mr-1 w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php $this->push('scripts') ?>
<script>
function tableEditor(config) {
    return {
        tableId: config.tableId,
        entries: config.entries,
        diceExpression: config.diceExpression,
        minPossible: 1,
        maxPossible: 20,
        saveTimeout: null,
        pendingChanges: false,
        dirtyEntries: false,
        draggedIndex: null,
        dataVersion: null,
        
        init() {
            // Parse dice expression and initialize ranges
            this.initDiceRange();
            
            // Initial fetch to get the current version
            this.refreshFromServer();
            
            // Set up refresh on visibility change (when user returns to tab)
            document.addEventListener('visibilitychange', () => {
                if (document.visibilityState === 'visible' && !this.pendingChanges && !this.dirtyEntries) {
                    this.refreshFromServer();
                }
            });
            
            // Refresh data every 30 seconds if the page is visible and no edits in progress
            setInterval(() => {
                if (document.visibilityState === 'visible' && !this.pendingChanges && !this.dirtyEntries) {
                    this.refreshFromServer();
                }
            }, 30000);
        },
        
        // Fetch fresh data from the server
        async refreshFromServer() {
            try {
                const response = await fetch(`${window.location.origin}<?= $basePath ?>/api/tables/${this.tableId}/entries`, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json'
                    },
                    cache: 'no-store' // Always fetch fresh data
                });
                
                if (!response.ok) {
                    console.error('Failed to refresh data from server');
                    return;
                }
                
                const result = await response.json();
                if (result && result.entries) {
                    console.log('Refreshed data from server');
                    this.entries = result.entries;
                    // Store the version for optimistic concurrency control
                    this.dataVersion = result.version;
                    console.log('Data version:', this.dataVersion);
                }
            } catch (error) {
                console.error('Error refreshing data:', error);
            }
        },
        
        initDiceRange() {
            // Parse dice expression like "3d6", "d20", etc.
            const match = this.diceExpression.match(/(\d+)?d(\d+)([+-]\d+)?/);
            if (match) {
                const numDice = parseInt(match[1] || 1);
                const diceType = parseInt(match[2]);
                const modifier = parseInt(match[3] || 0);
                
                this.minPossible = numDice + modifier;
                this.maxPossible = (numDice * diceType) + modifier;
            }
            
            // Sort entries by min_value
            this.entries.sort((a, b) => a.min_value - b.min_value);
            
            // Add beforeunload event to warn about unsaved changes
            window.addEventListener('beforeunload', (event) => {
                if (this.pendingChanges || this.dirtyEntries) {
                    // Standard way of showing a confirmation dialog before leaving
                    event.preventDefault();
                    event.returnValue = '';
                    return '';
                }
            });
        },
        
        // Debounce function to prevent excessive API calls
        debounce(func, delay = 1000) {
            // Mark that we have pending changes
            this.pendingChanges = true;
            
            // Clear any existing timeout
            clearTimeout(this.saveTimeout);
            
            // Set a new timeout
            this.saveTimeout = setTimeout(() => {
                func();
                this.pendingChanges = false;
            }, delay);
        },
        
        fixRange(entry) {
            // Handle empty or invalid inputs
            if (entry.min_value === '' || entry.min_value === null || isNaN(entry.min_value)) {
                entry.min_value = '';
                return; // Don't save if min_value is empty
            }
            if (entry.max_value === '' || entry.max_value === null || isNaN(entry.max_value)) {
                entry.max_value = '';
                return; // Don't save if max_value is empty
            }

            // Convert to numbers for comparison
            entry.min_value = Number(entry.min_value);
            entry.max_value = Number(entry.max_value);
            
            // Ensure values are within possible range
            entry.min_value = Math.max(this.minPossible, Math.min(this.maxPossible, entry.min_value));
            entry.max_value = Math.max(this.minPossible, Math.min(this.maxPossible, entry.max_value));
            
            // Ensure min_value <= max_value
            if (entry.min_value > entry.max_value) {
                entry.max_value = entry.min_value;
            }
            
            // Mark that we have changes to save
            this.dirtyEntries = true;
            
            // Debounce the save operation - will collect all changes
            this.debounce(() => this.saveEntries());
        },
        
        findNextAvailableRange() {
            let current = this.minPossible;
            
            // Sort entries by min_value
            const sortedEntries = [...this.entries].sort((a, b) => a.min_value - b.min_value);
            
            for (const entry of sortedEntries) {
                if (current < entry.min_value) {
                    return { min: current, max: entry.min_value - 1 };
                }
                current = Math.max(current, entry.max_value + 1);
            }
            
            if (current <= this.maxPossible) {
                return { min: current, max: this.maxPossible };
            }
            
            return null;
        },
        
        autofillRange(entry) {
            const range = this.findNextAvailableRange();
            if (range) {
                entry.min_value = range.min;
                entry.max_value = range.max;
                this.saveEntries();
            }
        },
        
        quickFill() {
            // Create entries for each possible value in the dice range
            const existingValues = new Set(this.entries.flatMap(entry => {
                const values = [];
                for (let i = entry.min_value; i <= entry.max_value; i++) {
                    values.push(i);
                }
                return values;
            }));
            
            // Find missing values and create entries for them
            const newEntries = [];
            for (let value = this.minPossible; value <= this.maxPossible; value++) {
                if (!existingValues.has(value)) {
                    newEntries.push({
                        min_value: value,
                        max_value: value,
                        result: `Result for ${value}`
                    });
                }
            }
            
            // Add all new entries
            if (newEntries.length > 0) {
                this.entries.push(...newEntries);
                this.entries.sort((a, b) => a.min_value - b.min_value);
                this.saveEntries();
            }
        },
        
        addEntry() {
            const range = this.findNextAvailableRange();
            this.entries.push({
                min_value: range ? range.min : this.minPossible,
                max_value: range ? range.max : this.minPossible,
                result: ''
            });
            // Mark as dirty and save immediately for add operations
            this.dirtyEntries = true;
            this.saveEntries();
        },
        
        removeEntry(index) {
            this.entries.splice(index, 1);
            // Mark as dirty and save immediately for remove operations
            this.dirtyEntries = true;
            this.saveEntries();
        },
        
        confirmDeleteAll() {
            if (this.entries.length === 0) {
                alert("There are no entries to delete.");
                return;
            }
            
            if (confirm("Are you sure you want to delete all entries? This action cannot be undone.")) {
                this.deleteAllEntries();
            }
        },
        
        deleteAllEntries() {
            // Clear all entries
            this.entries = [];
            // Mark as dirty and save immediately
            this.dirtyEntries = true;
            this.saveEntries();
        },
        
        async saveEntries() {
            if (!this.dirtyEntries && !this.pendingChanges) {
                return; // Don't save if nothing has changed
            }
            
            // Set pending changes to true to show the spinner
            this.pendingChanges = true;
            
            // Reset dirty flag
            this.dirtyEntries = false;
            
            try {
                const payload = { 
                    entries: this.entries,
                    version: this.dataVersion // Include version for optimistic concurrency control
                };
                console.log('Sending payload to server:', payload);
                
                const response = await fetch(`${window.location.origin}<?= $basePath ?>/api/tables/${this.tableId}/entries`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload),
                    // Always fetch fresh data, never use cache
                    cache: 'no-store'
                });
                
                const responseText = await response.text();
                console.log('Server response:', responseText);
                
                if (!response.ok) {
                    let errorMessage = 'Failed to save entries';
                    let outdated = false;
                    let serverData = null;
                    
                    try {
                        const errorData = JSON.parse(responseText);
                        errorMessage = errorData.error || errorMessage;
                        outdated = errorData.outdated || false;
                        serverData = errorData.entries || null;
                        
                        // If we got a version conflict (409 Conflict) and server sent updated data
                        if (response.status === 409 && outdated && serverData) {
                            if (confirm("Your data is outdated. Someone else has updated this table. Do you want to:\n\n" +
                                       "- OK: Discard your changes and load the latest data\n" +
                                       "- Cancel: Keep your changes and try to save again")) {
                                // User chose to discard their changes and use server data
                                this.entries = serverData;
                                this.dataVersion = errorData.version;
                                console.log('Updated to server version:', this.dataVersion);
                                return; // Exit without showing error
                            } else {
                                // User chose to keep their changes
                                // Mark as dirty so they can try to save again
                                this.dirtyEntries = true;
                                return; // Exit without showing error
                            }
                        }
                    } catch (e) {
                        console.error('Failed to parse error response:', e);
                    }
                    throw new Error(errorMessage);
                }
                
                try {
                    const result = JSON.parse(responseText);
                    if (!result || !result.entries) {
                        throw new Error('Invalid response format from server');
                    }
                    
                    console.log('Received entries from server:', result.entries);
                    
                    // Always update with server data to ensure consistency
                    this.entries = result.entries;
                    
                    // Update the version
                    this.dataVersion = result.version;
                    console.log('Updated to new version:', this.dataVersion);
                    
                    // Force a small delay to ensure UI updates
                    await new Promise(resolve => setTimeout(resolve, 100));
                    
                } catch (e) {
                    console.error('Failed to parse success response:', e);
                    throw new Error('Invalid response format from server');
                }
            } catch (error) {
                console.error('Error saving entries:', error);
                alert('Failed to save changes: ' + error.message);
                // Mark as dirty again so we retry on next save
                this.dirtyEntries = true;
            } finally {
                // Always clear the pending changes flag
                this.pendingChanges = false;
            }
        },

        autofillAllRanges() {
            // Get the total range size
            const totalRange = this.maxPossible - this.minPossible + 1;
            const entryCount = this.entries.length;
            
            // If no entries, nothing to do
            if (entryCount === 0) return;
            
            // Calculate the ideal size for each range
            const idealRangeSize = totalRange / entryCount;
            
            // Create a copy of entries for working with
            const workingEntries = [...this.entries];
            
            // Reset all entries to have no range
            this.entries.forEach(entry => {
                entry.min_value = '';
                entry.max_value = '';
            });
            
            // Special case: if only one entry, give it the full range
            if (entryCount === 1) {
                this.entries[0].min_value = this.minPossible;
                this.entries[0].max_value = this.maxPossible;
                this.saveEntries();
                return;
            }
            
            // Calculate ranges using a more sophisticated distribution algorithm
            let remainingValues = totalRange;
            let remainingEntries = entryCount;
            let currentValue = this.minPossible;
            
            // Distribute ranges as evenly as possible
            for (let i = 0; i < entryCount; i++) {
                const entry = this.entries[i];
                
                // Calculate size for this range
                // For the last entry, assign all remaining values
                let rangeSize;
                if (i === entryCount - 1) {
                    rangeSize = remainingValues;
                } else {
                    // Calculate fair share of remaining values
                    rangeSize = Math.ceil(remainingValues / remainingEntries);
                    
                    // Ensure we don't create ranges that are too small at the end
                    const minRangeSize = Math.max(1, Math.floor(idealRangeSize * 0.7));
                    rangeSize = Math.max(minRangeSize, rangeSize);
                    
                    // Don't exceed remaining values
                    rangeSize = Math.min(rangeSize, remainingValues - (remainingEntries - 1));
                }
                
                // Assign range to this entry
                entry.min_value = currentValue;
                entry.max_value = currentValue + rangeSize - 1;
                
                // Update tracking variables
                currentValue += rangeSize;
                remainingValues -= rangeSize;
                remainingEntries--;
            }
            
            // Ensure the last entry covers up to maxPossible
            if (this.entries[entryCount - 1].max_value < this.maxPossible) {
                this.entries[entryCount - 1].max_value = this.maxPossible;
            }
            
            // Save the changes
            this.saveEntries();
        },

        startDrag(index, event) {
            this.draggedIndex = index;
            
            // Set data for drag operation
            event.dataTransfer.effectAllowed = 'move';
            event.dataTransfer.setData('text/plain', index);
            
            // Add styling to the dragged element
            const element = event.target.closest('tr') || event.target.closest('div[draggable="true"]');
            if (element) {
                element.classList.add('opacity-50');
            }
        },
        
        endDrag(event) {
            // Remove styling when drag ends
            if (this.draggedIndex !== null) {
                const rows = document.querySelectorAll('tr.entry-row');
                rows.forEach(row => row.classList.remove('opacity-50', 'bg-indigo-50'));
                
                const mobileCards = document.querySelectorAll('div[draggable="true"]');
                mobileCards.forEach(card => card.classList.remove('opacity-50', 'bg-indigo-50'));
                
                this.draggedIndex = null;
            }
        },
        
        dragOver(index, event) {
            event.preventDefault();
            
            // Don't do anything if hovering over the same item being dragged
            if (this.draggedIndex === index) {
                return;
            }
            
            // Add hover styling
            const rows = document.querySelectorAll('tr.entry-row');
            rows.forEach(row => row.classList.remove('bg-indigo-50'));
            
            const mobileCards = document.querySelectorAll('div[draggable="true"]');
            mobileCards.forEach(card => card.classList.remove('bg-indigo-50'));
            
            const element = event.target.closest('tr') || event.target.closest('div[draggable="true"]');
            if (element) {
                element.classList.add('bg-indigo-50');
            }
        },
        
        drop(index, event) {
            event.preventDefault();
            
            // Get the dragged index
            const fromIndex = this.draggedIndex;
            const toIndex = index;
            
            // Don't do anything if dropping on the same item
            if (fromIndex === toIndex) {
                return;
            }
            
            // Move the item in the array
            const item = this.entries.splice(fromIndex, 1)[0];
            this.entries.splice(toIndex, 0, item);
            
            // Reset drag state
            this.draggedIndex = null;
            
            // Remove all styling
            const rows = document.querySelectorAll('tr.entry-row');
            rows.forEach(row => row.classList.remove('opacity-50', 'bg-indigo-50'));
            
            // Save the new order
            this.dirtyEntries = true;
            this.saveEntries();
        }
    };
}
</script>
<?php $this->end() ?> 