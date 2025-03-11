{assign var="id" value=rand(11111,99999)}
<div class="{$column_size}">
    <div class="panel">
        <div class="panel-heading">
            <span class="material-icons">bar_chart</span>
            <span class="title">{$title|escape:'htmlall'}</span>
        </div>
        <div class="panel-body">
            <canvas id="myChart_{$id}" width="400" height="200"></canvas>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        var ctx = document.getElementById('myChart_{$id}').getContext('2d');
        var chartType = '{$chartType|escape:'javascript'}';
        var chartData = {$data|json_encode};

        function createChart(type, data) {
            return new Chart(ctx, {
                type: type,
                data: data,
                options: {
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        }

        switch (chartType) {
            case 'bar':
                // For bar chart, data should be in format:
                // { labels: ['Label1', 'Label2', ...], datasets: [{ data: [value1, value2, ...] }] }
                createChart('bar', chartData);
                break;
            case 'line':
                // For line chart, data should be in same format as bar chart
                createChart('line', chartData);
                break;
            case 'pie':
                // For pie chart, data should be in format:
                // { labels: ['Label1', 'Label2', ...], datasets: [{ data: [value1, value2, ...] }] }
                createChart('pie', chartData);
                break;
            case 'doughnut':
                // For doughnut chart, data should be in same format as pie chart
                createChart('doughnut', chartData);
                break;
            default:
                console.error('Unsupported chart type');
        }
    </script>
</div>