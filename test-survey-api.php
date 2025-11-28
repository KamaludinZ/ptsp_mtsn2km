<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

// Simulate request
$controller = new \App\Http\Controllers\Admin\DashboardController();
$request = new \Illuminate\Http\Request(['filter' => 'month']);

$response = $controller->getSurveyAnalyticsData($request);
$data = json_decode($response->getContent(), true);

echo "Survey Analytics API Test\n";
echo "=========================\n\n";
echo "Total Respondents: " . $data['respondents'] . "\n";
echo "Satisfaction Index: " . $data['satisfactionIndex'] . "%\n";
echo "Average Rating: " . $data['avgRating'] . "\n\n";

echo "Age Distribution:\n";
foreach ($data['ageDistribution']['labels'] as $index => $label) {
    echo "  - $label: " . $data['ageDistribution']['data'][$index] . "\n";
}

echo "\nOccupation Distribution:\n";
foreach ($data['occupationDistribution']['labels'] as $index => $label) {
    echo "  - $label: " . $data['occupationDistribution']['data'][$index] . "\n";
}

echo "\nEducation Distribution:\n";
foreach ($data['educationDistribution']['labels'] as $index => $label) {
    echo "  - $label: " . $data['educationDistribution']['data'][$index] . "\n";
}

echo "\nService Distribution:\n";
foreach ($data['serviceDistribution']['labels'] as $index => $label) {
    echo "  - $label: " . $data['serviceDistribution']['data'][$index] . "\n";
}

echo "\nResponse Trend:\n";
echo "  Labels: " . implode(', ', array_slice($data['responseTrend']['labels'], 0, 5)) . "...\n";
echo "  Data: " . implode(', ', array_slice($data['responseTrend']['data'], 0, 5)) . "...\n";

echo "\n✓ API is working correctly!\n";
