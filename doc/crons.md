# Crons

Add this commands to your cron tab and don't forget to set the same PHP timezone "UTC" 
in  the php.ini file of php and php-cli.

## Required

1. Currencies update:

    `17 0 * * * php <path-to-your-app>bin/console cocorico:currency:update --env=dev`
2. Expire users:

   `0 */2 * * * php <path-to-your-app>bin/console cocorico:user:expire --env=dev`
3. Update post count:

   `0 */2 * * * php <path-to-your-app>bin/console cocorico:listing:update-count --env=dev`
4. Notify admins:

   `00 18 * * * php <path-to-your-app>bin/console cocorico:admin_notification --env=dev`
5. Expire posts:

   `0 */2 * * * php <path-to-your-app>bin/console cocorico:listing:expire --env=dev`


## Optionals

1. Generate Sitemap (Optional. ListingSeoBundle must be enabled)
    
    `0 4  * * *  php <path-to-your-app>bin/console cocorico_seo:sitemap:generate --env=dev`