# Commission Calculator

This project provides a solution for calculating commissions for transactions based on BIN numbers and currency conversions. The project is a refactoring and improvement of a legacy commission calculation script, now adhering to modern PHP practices and standards.

## Requirements

- PHP >= 7.4
- Composer

## Installation

1. **Clone the repository**:

    ```bash
    git clone <repository_url>
    ```

2. **Install dependencies**:

    ```bash
    composer install
    ```

## Usage

To calculate commissions, you can run the script with an input file:

```bash
php app.php input.txt
```

### Input Format

The input file should contain one transaction per line in JSON format. Each transaction should have the following fields:

- `bin`: The BIN (Bank Identification Number) of the card.
- `amount`: The amount of the transaction.
- `currency`: The currency of the transaction.

### Example Input File

```json
{"bin":"45717360","amount":"100.00","currency":"EUR"}
{"bin":"516793","amount":"50.00","currency":"USD"}
{"bin":"45417360","amount":"10000.00","currency":"JPY"}
{"bin":"41417360","amount":"130.00","currency":"USD"}
{"bin":"4745030","amount":"2000.00","currency":"GBP"}
```

## Testing
To run the tests, use PHPUnit:

```bash
composer test
```

## Code Style
To check the code style, use PHP CS Fixer:

```bash
composer cs-check
```

