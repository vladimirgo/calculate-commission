# Commission Calculator

Command-line application that calculates commission fees for credit card transactions based on the issuing country of the card (by its BIN) and the transaction currency.

## Requirements

*   PHP >= 7.4
*   Composer
*   An API Key for the exchange rate service from https://exchangeratesapi.io/ (free plan available).

## Setup & Installation

1.  **Clone the repository:**
    ```bash
    git clone https://github.com/vladimirgo/calculate-commission.git
    cd calculate-commission
    ```

2.  **Install dependencies:**
    ```bash
    composer install
    ```

3.  **Configure Environment Variables:**
    *   Copy the example environment file:
        ```bash
        cp .env.example .env
        ```
    *   Edit the `.env` file and add your API key for the exchange rate service:
        ```
        ER_API_KEY=your_actual_api_key_here
        ```

## Usage

*   Run application:
    ```bash
    php app.php input.txt
    ```
*   Run tests:
    ```bash
    composer test
    ```
