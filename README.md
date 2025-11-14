![alt text]([https://jasperphp.net/wp-content/uploads/2020/01/cropped-ms-icon-150x150-2.png](https://github.com/QuilhaSoft/JasperPHP/blob/master/images/jasperLogo.png)) 

# JasperPHP
A pure PHP library to generate reports from JasperSoft Studio (.jrxml files), without the need for a Java bridge or a Jasper Server.

[![Donate](https://img.shields.io/badge/Donate-PayPal-green.svg)](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=EE7CD4UZEL3A4&source=url)

For more details, documentation, and blog posts, visit [jasperphp.net](https://jasperphp.net).

## Recent Changes & Modernization
This project has undergone a significant modernization effort to align with current PHP best practices. Key changes include:
- **Composer Integration:** The project now uses Composer for dependency management.
- **PSR-4 Autoloading:** Switched to PSR-4 for class autoloading, with a reorganized and namespaced directory structure (`src/`).
- **Static Analysis:** `phpstan` has been integrated to improve code quality and catch errors.
- **Flexible Data Sources:** Added support for multiple data sources, including Arrays, JSON/CSV files, and direct database queries.
- **Versatile Output Methods:** Introduced new methods to stream reports to the browser, force downloads, save to a file, or get the content as a base64 string.

## Requirements
- PHP 7.4 or higher
- Composer for dependency management.

The following PHP extensions are also required:
- `gd`
- `mbstring`
- `xml`

## Installation
Install the library using Composer:
```bash
composer require quilhasoft/jasperphp:dev-master
```

## Quick Start
Here is a basic example of how to generate a report. For a more detailed and runnable example, see `public/index.php`.

```php
<?php

require_once __DIR__ . '/vendor/autoload.php';

use JasperPHP\core\TJasper;

// Path to your .jrxml file
$reportFile = __DIR__ . '/path/to/your/report.jrxml';

// Report parameters (if any)
$params = ['title' => 'My Report'];

// Data source configuration
$dataSource = [
    'type' => 'array',
    'data' => [
        ['id' => 1, 'name' => 'Product A', 'price' => 10.50],
        ['id' => 2, 'name' => 'Product B', 'price' => 22.00],
    ]
];

try {
    // Instantiate the report
    $jasper = new TJasper($reportFile, $params, $dataSource);

    // Generate and output the report to the browser
    // The output() method handles the entire process
    $jasper->output(); // Default output is PDF inline

} catch (\Exception $e) {
    echo 'Error generating report: ' . $e->getMessage();
}
```

## Data Sources
You can use different types of data sources to populate your reports.

### Array
Pass an array of objects or associative arrays directly.
```php
$dataSource = [
    'type' => 'array',
    'data' => [
        (object)['id' => 1, 'name' => 'Item A'],
        (object)['id' => 2, 'name' => 'Item B']
    ]
];
```

### Database (DB)
Execute a SQL query to fetch data.
```php
$dataSource = [
    'type' => 'db',
    'sql' => 'SELECT * FROM customers',
    'db_driver' => 'mysql',
    'db_host' => 'localhost',
    'db_port' => '3306',
    'db_name' => 'mydatabase',
    'db_user' => 'user',
    'db_pass' => 'password',
];
```

### JSON or CSV File
Load data from a local `.json` or `.csv` file.
```php
// From a JSON file
$dataSource = [
    'type' => 'json_file',
    'path' => '/path/to/your/data.json'
];

// From a CSV file
$dataSource = [
    'type' => 'csv_file',
    'path' => '/path/to/your/data.csv'
];
```

### Using Embedded SQL Query (from JRXML)
The library also retains the classic JasperReports functionality of executing a SQL query embedded directly within the `.jrxml` file. When no `dataSource` is provided in the PHP code, JasperPHP will look for a `<queryString>` tag inside the report file and execute it using the provided database connection.

This method is especially useful for creating master-detail reports, where a subreport can fetch its own data based on parameters passed from the main report.

**Example JRXML (`subreport.jrxml`):**
```xml
...
<parameter name="CUSTOMER_ID" class="java.lang.Integer"/>
<queryString>
    <![CDATA[SELECT * FROM orders WHERE customer_id = $P{CUSTOMER_ID}]]>
</queryString>
<field name="order_date" class="java.util.Date"/>
<field name="order_total" class="java.math.BigDecimal"/>
...
```

**Example PHP:**
To run a report with an embedded query, provide the database connection details but omit the `'sql'` key from the `dataSource`.

```php
$dbConfig = [
    'type' => 'db',
    // No 'sql' key is needed here
    'db_driver' => 'mysql',
    'db_host' => 'localhost',
    'db_name' => 'mydatabase',
    'db_user' => 'user',
    'db_pass' => 'password',
];

// Parameters needed by the query in the JRXML
$reportParams = [
    'CUSTOMER_ID' => 123
];

$jasper = new TJasper('report_with_query.jrxml', $reportParams, $dbConfig);
$jasper->output();
```

## Output Methods
The `output()` method provides several ways to deliver the generated report.

```php
public function output(string $mode = 'I', string $filename = 'report.pdf', ?string $filePath = null): ?string
```

- **`$mode`**:
  - `I` (Inline): Streams the report directly to the browser. (Default)
  - `D` (Download): Forces the browser to download the report file.
  - `F` (File): Saves the report to a local file specified by `$filePath`.
  - `S` (String): Returns the raw report content as a string (or base64 encoded for binary formats).
- **`$filename`**: The name of the file for `I` and `D` modes.
- **`$filePath`**: The absolute path to save the file in `F` mode.

### Examples:
```php
// Stream to browser
$jasper->output('I', 'my_report.pdf');

// Force download
$jasper->output('D', 'invoice.pdf');

// Save to a file
$jasper->output('F', 'report.pdf', '/path/to/save/report.pdf');

// Get as a string
$reportContent = $jasper->output('S');
```

## Supported Formats
- PDF
- XLS
- XLSX

## Supported JRXML Elements
The library supports a wide range of JRXML tags and components.

| TAG/Component   | Status | TAG/Component   | Status |
|-----------------|--------|-----------------|--------|
| **Basic Elements** | | | |
| Text Field      | OK     | Static Text     | OK     |
| Image           | OK     | Break           | OK     |
| Rectangle       | OK     | Line            | OK     |
| SubReport*      | OK     | Barcode         | OK     |
| **Composite Elements** | | | |
| Page Number     | OK     | Total Pages     | OK     |
| Current Date    | OK     | Page X of Y     | OK     |
| **Bands**       | | | |
| Title           | OK     | Page Header     | OK     |
| Group           | OK     | Detail          | OK     |
| Column Header   | OK     | Column Footer   | OK     |
| Page Footer     | OK     | Summary         | OK     |
| Background      | OK     | Style           | OK     |
| Frame           | OK     | Dynamic Table   | OK     |

*\* Subreports are supported recursively and without limits.*

## Other Features
- Aggregation functions for variables (sum, average, min, max).
- Reading and calculating variables from subreports.
- Conditional styling.
- Support for Laravel DB Facade by setting the `net.sf.jasperreports.data.adapter` property in your JRXML.

## License
This library is licensed under the MIT License.