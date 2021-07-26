# Magento 2 Two Factor Authentication Extension FREE

[Magento 2 Two-Factor Authentication](http://www.mageplaza.com/magento-2-two-factor-authentication/) from Mageplaza is built to ensure the highest security for your Magento 2 stores. The extension can force using 2FA or auto skip 2FA request for trusted devices. Mobile compatibility is also supported in this module.  

## 1. Documentation

- [Installation guide](https://www.mageplaza.com/install-magento-2-extension/)
- [User guide](https://docs.mageplaza.com/two-factor-authentication/index.html)
- [Introduction page](http://www.mageplaza.com/magento-2-two-factor-authentication/)
- [Contribute on Github](https://github.com/mageplaza/magento-2-two-factor-authentication)
- [Get Support](https://github.com/mageplaza/magento-2-two-factor-authentication/issues)

## 2. FAQ

**Q: I got error: Mageplaza_Core has been already defined**

A: Read solution [here](https://github.com/mageplaza/module-core/issues/3)

**Q: How many steps admin has to pass to access admin data?**

A: There are two steps. The first is simple with username and password, the second is authentication code provided by the mobile authentication app

**Q: Which apps can I use for 2FA?**

A: We recommend you use Authy and Google Authentication for the best result.

**Q: If I do not want to be required 2FA the next time, how can I do?**

A: You can do by enabling the trusted device function and set the trusted time by days. Then, in the first login, click on Trust this device for x days. It can be done properly.  

**Q: I am a store owner. Our store has many admins. How can I set 2FA for specific accounts only?**

A: Kindly follow this guide. Firstly, turn off Forcing to use 2FA function. Then the admin accounts which is not set as a trusted device and turn on 2FA will have to use 2FA. 

**Q: Can I know the list of trusted device and remove any accounts if any changes require?**

A: Yes, you can easily see from admin backend and click on remove button to do any removing accounts. 

## 3. How to install Two-Factor Authentication extension for Magento 2

Install via composer (recommend): Run the following command in Magento 2 root folder:

With Marketing Automation (recommend):
```
composer require mageplaza/module-two-factor-authentication
php bin/magento setup:upgrade
php bin/magento setup:static-content:deploy
```

Without Marketing Automation:
```
composer require mageplaza/module-two-factor-authentication
php bin/magento setup:upgrade
php bin/magento setup:static-content:deploy
```


For versions below Magento version 2.4.0, it requires to install the library of bacon-qr-code via composer by the following command

```
composer require bacon/bacon-qr-code
```

## 4. Highlight Features of Magento 2 Two Factor Authentication

### Two steps to access

![Two steps to access](https://i.imgur.com/8qZZicH.png)

#### Forcing to use Two-factor authentication  

[Magento 2 Two Factor Authentication](https://marketplace.magento.com/mageplaza-module-two-factors-authentication.html) (2FA) supports backend store data to be better protected with two steps of verification. If forcing feature is enable, admins are required to set up two-factor authentication before they have the ability to access all data from backend panel. 

#### Support from mobile authentication apps
To activate two-factor authentication, the support from mobile authentication apps is needed. Admins need to download apps such as Authy, Duo, Google Authentication. After registering authenticator accounts by scanning QR code or manually entering the provided key, the app will create a unique verification code which is used to confirm the admin account. 


### No requirement if being trusted 

![Magento 2 Two Factor Authentication](https://i.imgur.com/NRYkNWv.png)

#### Activate trusted device function, set trusted time 
To save time for trusted admin accounts after the first time login, Trusted device function is supported. After this feature is configured well, via a click to require trust for next login, the device will be listed to trusted list and not be required authentication code in a specific time. 

#### Quick login without authentication code in the next login 
As a result,  after the first time confirming the account successfully, as long as within the trusted time, the second verification is not required for the next login times. With this feature, it is time-saving for key store admins whose accounts are believed to be reliable.  

### Trusted device list 

![Magento 2 Two Factor Authentication](https://i.imgur.com/BTCvGnz.png)

It is easy to manage all trusted verified admin roles by the Trusted Device list. The information of logged users are recorded clearly with the following details:

- Device Name
- IP address
- Address
- Last login time 

Besides, super admin or store owners can easily remove any admin accounts from the trusted device in case there is any account updates. Therefore, admin panel can be protected well from the ill-intentioned access. 

## 5. More Features of Magento 2 2FA

### Force Using 2FA 
Enable/ Disable requiring users to register 2FA

### Trusted Time 
Set trusted time for user accounts by days 

### Mobile friendly 
Be well responsive to mobiles, desktop, tablets, and other screen sizes.

## 6. Full Features List

### General Configuration

- Enable/ Disable the extension 
- Force admins to use 2FA 
- Enable/ Disable Trusted Device 
- Set trusted time by days

### Admin account setting 2FA

- Setting account information: User name, Email, password 
- Enable/ Disable 2FA for the account 
- Input confirmation code from the authentication app
- Use a unique authentication code for each time login
- Click on trust this device when login to save second authentication confirmation for a specific days
- View Trusted Device list 
- Remove an admin account from the Trusted Device list

## 7. Magento 2 Two Factor Authentication User Guide

### How to use Two Factor Authentication

When logging in the backend, admin users need to fill in the authentication factors

![Magento 2 Two Factor Authentication](https://i.imgur.com/eD6CJJZ.png)

When turn on Trusted Device, authentication request page looks like this:

![Magento 2 Two Factor Authentication](https://i.imgur.com/LTusIgC.png)


### How to Configure Two Factor Authentication

#### 7.1. Configuration

From **Admin panel**, go to `Stores > Configuration > Mageplaza > Two factor Authentication`

![Magento 2 Two Factor Authentication](https://i.imgur.com/6V0ncoR.png)

- **Enable**: Select `Yes` to activate the module

- **Force Using 2FA**: 
  - Choose `Yes` to force all admin users to register Two-Factor Authentication (2FA). If the account logged in has not yet installed 2FA in the account setting, it will be linked to the **Account setting** page for installation
  - When 2FA is enable, all admin users who have not registered 2FA must go to **My Account** page to set it up. After that, they can access others admin pages
  
- **Enable Trusted Device**: 
  - Select `Yes` to enable saving the trusted devices. In a certain period of time, when logging in with this device, admin users do not need to authenticate the two factors
  - This certain period is configured at **Trusted Time** field
  
- **Trusted Time**: 
  - During the time period set in this section, when logging in with this device, the admin users do not need to authenticate two factors. 
  - When changing **Trust time**, the previously saved devices also change the trust time accordingly
  - Time is set by day
  
- **Whitelist(s)**:
  - Only the IP addresses filled in this section can access the Dashboard page without 2FA (even if not in the Trust Device List)
  - It is possible to allow 1 IP address, multiple IP addresses, 1 range of IP addresses or multiple IP address ranges to have access to admin. IP addresses are separated by commas
  - The owner can also allow IP addresses to be accessible to admin pages without authenticating 2FA in the following form:
10.0.0.10, 10.0.0. *, 10.0. *. *, 10.0.0. * - 123.0.0. *, 12.3. *. * - 222.0. *. *
Symbol "*" in range 0 - 255
  
  
#### 7.2. My Account Admin

Admins need to go to **Account Setting** to set **QR/Pin code**

![](https://i.imgur.com/5s0e8hG.png)

##### **Register 2FA**:

![Magento 2 Two Factor Authentication](https://i.imgur.com/pXtezSd.png)

- After enabling 2FA, admins need to use the **Authy app or Google Authenticator** on the phone to scan the QR code or enter the Key into the app to get the confirmation code.
- After QR code is saved in the app, it automatically generates confirmation code. Admin needs to get that code and enter the it to register
- After registering, from the next login, admins need to get the code from the app to verify so that they can access the dashboard
- The confirmation code created by the app after being replaced 30s still works for verification
- When **Force using 2FA** is enabled, the admin user cannot disable 2FA here


##### **Check and remove Trusted Devices**:

![Magento 2 Two Factor Authentication](https://i.imgur.com/rG9dRFD.png)


- Log the browser on the machine with certain IPs that can be trusted and the last time the user logs in with this browser
- When the enable trust device, in the trust time period, the devices saved here will not need to enter the confirmation code to log on.
- Over time of trust time, device will be automatically removed from the list
- User admin can also remove that period by clicking the `Remove` button

**Get more Free extension on Github:**
- [Magento 2 SEO extension](https://github.com/mageplaza/magento-2-seo)
- [Magento 2 Social Login](https://github.com/mageplaza/magento-2-social-login)
- [Magento 2 Product Slider](https://github.com/mageplaza/magento-2-product-slider)
- [Magento 2 Gdpr](https://github.com/mageplaza/magento-2-gdpr)
- [Magento 2 Security extension](https://github.com/mageplaza/magento-2-security)
- [Magento 2 Google ReCaptcha](https://github.com/mageplaza/magento-2-google-recaptcha)
- [Magento 2 Blog extension](https://github.com/mageplaza/magento-2-blog)
- [Magento 2 Twitter Widget](https://github.com/mageplaza/magento-2-twitter-widget)
- [Magento 2 Banner Slider](https://github.com/mageplaza/magento-2-banner-slider)

**[Explore Magento 2 modules on Marketplace](https://marketplace.magento.com/partner/Mageplaza):**
- [Magento 2 Currency Formatter](https://marketplace.magento.com/mageplaza-module-currency-formatter.html)
- [Magento 2 Multi Flat Rates](https://marketplace.magento.com/mageplaza-module-multi-flat-rates.html)
- [Magento 2 Name Your Price](https://marketplace.magento.com/mageplaza-module-name-your-price.html)
- [Magento 2 Instagram Feed](https://marketplace.magento.com/mageplaza-module-instagram-feed.html)
- [Magento 2 Share Cart](https://marketplace.magento.com/mageplaza-module-share-cart.html)
- [Magento 2 Same Order Number](https://marketplace.magento.com/mageplaza-module-same-order-number.html)
- [Magento 2 One Step Checkout](https://marketplace.magento.com/mageplaza-magento-2-one-step-checkout-extension.html)
- [Magento 2 PDF Invoice](https://marketplace.magento.com/mageplaza-module-pdf-invoice.html)
- [Magento 2 Auto Related Products](https://marketplace.magento.com/mageplaza-module-automatic-related-products.html)
- [Magento 2 SEO extension](https://marketplace.magento.com/mageplaza-magento-2-seo-extension.html)
- [Magento 2 Gift Card](https://marketplace.magento.com/mageplaza-module-gift-card.html)
