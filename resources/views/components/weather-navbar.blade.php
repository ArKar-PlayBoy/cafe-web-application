@auth
<div id="navbar-weather" class="flex items-center gap-2 px-3 py-1.5 bg-gray-100 dark:bg-gray-700 rounded-full text-sm">
    <span id="navbar-weather-icon" class="text-lg">&#9728;</span>
    <span id="navbar-weather-temp" class="font-medium text-gray-700 dark:text-gray-200">--&deg;C</span>
    <span id="navbar-weather-condition" class="inline text-gray-500 dark:text-gray-400 text-xs">--</span>
</div>

<script>
(function() {
    const iconEl = document.getElementById('navbar-weather-icon');
    const tempEl = document.getElementById('navbar-weather-temp');
    const conditionEl = document.getElementById('navbar-weather-condition');
    const icons = {
        0: '\u2600\uFE0F',
        1: '\uD83C\uDF24\uFE0F',
        2: '\u26C5',
        3: '\u2601\uFE0F',
        45: '\uD83C\uDF2B\uFE0F',
        48: '\uD83C\uDF2B\uFE0F',
        51: '\uD83C\uDF27\uFE0F',
        53: '\uD83C\uDF27\uFE0F',
        55: '\uD83C\uDF27\uFE0F',
        61: '\uD83C\uDF27\uFE0F',
        63: '\uD83C\uDF27\uFE0F',
        65: '\uD83C\uDF27\uFE0F',
        80: '\uD83C\uDF26\uFE0F',
        81: '\uD83C\uDF26\uFE0F',
        82: '\uD83C\uDF26\uFE0F',
        95: '\u26C8\uFE0F',
        96: '\u26C8\uFE0F',
        99: '\u26C8\uFE0F'
    };

    function setUnavailableState() {
        if (tempEl) tempEl.textContent = '--\u00B0C';
        if (conditionEl) conditionEl.textContent = 'Unavailable';
    }

    fetch('/api/weather')
        .then(function(response) {
            if (!response.ok) {
                throw new Error('Weather request failed');
            }

            return response.json();
        })
        .then(function(data) {
            if (!data || typeof data !== 'object') {
                throw new Error('Invalid weather payload');
            }

            if (tempEl) tempEl.textContent = (data.temperature != null ? Math.round(data.temperature) : '--') + '\u00B0C';
            if (conditionEl) conditionEl.textContent = data.condition || '--';
            if (iconEl) iconEl.textContent = icons[data.code] || '\uD83C\uDF24\uFE0F';
        })
        .catch(function() {
            setUnavailableState();
        });
})();
</script>
@endauth
