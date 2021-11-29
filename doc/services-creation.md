# Create services accounts

## Create your Google API account

* Go to https://console.developers.google.com/project
* Sign-in with you google account
* Create a new project
* Activate the following APIs
    - Google Places API Web Service
    - Google Maps JavaScript API
    - Google Maps Geocoding API
* Create a Browser API Key and add your domain to the white list
* Create a Server API Key and add your server IP to the white list

In the next chapter "Install Cocorico dependencies" you will add respectively the "Browser API Key" 
and the "Server API Key" to the `cocorico_geo.google_place_api_key` and `cocorico_geo.google_place_server_api_key` 
parameters in `app/config/parameters.yml`.


*Note: Starting January 31 2018 the Places Web Service API will no longer accept API Keys with HTTP Referer usage restrictions.*
*See https://developers.google.com/maps/faq#switch-key-type.*

## Create Google reCAPTCHA

* Go to https://www.google.com/recaptcha/admin/create
* Sign in with your Google account
* Create a new **v3** Google reCAPTCHA
* Set a domain to the main website domain (you don't have to include subdomains) 
  * _e.g. azimuth-weconnect.eu_
* Put your **"Site key"** to `cocorico.google_recaptcha_site_key` parameter in `app/config/parameters.yml`.