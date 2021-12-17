module.exports = {
    '**/*.php': [
        'php ./vendor/bin/phpcs --colors --style=./phpcs.xml',
        'php ./vendor/bin/phpstan analyze',
    ],
};
