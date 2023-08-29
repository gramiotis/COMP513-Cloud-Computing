# Dockerized eshop that is deployable to the cloud
This is a eshop web app that was containerised using Docker. The functions 
that supports are:
* Account creation
* Account authentication through a login page
* Different roles for each account (User, Seller, Admin)
* Product page where you can browse the product catalog and add products to
your cart
* Cart page where you can see the added products and the total cost
* Seller page where a seller uploads their products
* Admin page for account management
* Notifications system for products
* Asynchronised requests to server when interacting with the app using AJAX 
* Basic security features for the protection of the app

## Services used
The services that were used to implement the above features were:
- *Apache web server*
- *MySQL* for storing account info
- *Keyrock IDM* for account management
- *MongoDB* for storing product catalog and notifications
- *PEPProxy* for secured communication between the services
- *Orion Context Broker* for the notification system
- *PHPMyAdmin*

For more info about the architecture of the web app visit [here](https://github.com/gramiotis/COMP513-Cloud-Computing/blob/main/Report.pdf)

