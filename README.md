# SampleApp-Webhooks-PHP
Welcome to Intuit Developer's sample application for using Webhooks with the QuickBooks Online APIs and PHP. 

This sample code is meant to provide you fulling working examples of how to implement a successful webhook connection as well as the best practices for doing so. 

## Step 0: Get Ready
Before we get started, we are going to need a couple things:
 * An [Intuit Developer](https://developer.intuit.com/) account
 * An [Intuit Developer app](https://developer.intuit.com/hub/blog/2015/11/10/creating-your-first-app-with-intuit-developer) with OAuth credentials
 * Familiarity with PHP, and a way to host and test your code
 * This repository cloned on your local machine

## Step 1: Set up ngrok
In order to setup your webhook, you need an https url, which can be tricky when developing on a local machine. To get around this we will be using a free service called [ngrok](https://ngrok.com/). You could also use any other means that puts your code in a https url. 
 1. Head to [ngrok.com](https://ngrok.com/) and download the zip for your operating system.
 2. Unzip it and move it to a conveniently findable location
 3. Open up a terminal window and navigate to the folder that you put ngrok
 4. run `$ ./ngrok http 8080` where you would replace `8080` with which ever port on your localhost you would want to expose to the world. For example, I was hosting my code at `http://localhost:14080/` so I ran `$ ./ngrok http 14080`
 5. You should now see a black screen with a ngrok.io url that your local machine is exposed on. 

 ## Step 2: Configure your Webhook
 Log into your Intuit Developer account and navigate into the application you are developing. Click on settings and scroll down to Webhooks. Paste in the URL from ngrok, (or wherever you have your code exposed). Note: this URL must start with `https://` which ngrok should supply to you automatically. This is also where you will select which events will trigger a notification to you app (selecting them all makes debugging easiest). After you are done, click the 'Show Token' button and copy that token, you'll need it for the next step.


## Step 3: Fill in your app's credentials
Locate the `credentials-sample.json` file and make a copy of it titled `credentials.json` and fill in with the appropriate credentials from your app on [developer.intuit.com](https://developer.intuit.com/) as well as the verifier token that you copied and the https URL that your app is hosted.


 ## Step 4: Connect your app to a sandbox account
 Load your page, either from a local host or your exposed URL. You should be immediately prompted to log into your Intuit account, and connect a sandbox application. If everything goes well you should see "Congratulations, your app is now connected!"

 ## Step 5: Test your webhook
 Launch the sandbox company that you connected to your app from your developer account. Make a change like creating a new customer or paying an invoice

 ## Step 6: Wait 5 minutes
 Notifications are batched, so you will only get notified every five minutes. 

 ## Step 7: Check your logs
 Because you don't see the response of a webhook notification, you won't know that it happened unless you log the request. After you get a notification you can check the `request.log` file to see some of the details as well as the payload of your request. If everything went well you should see a log similar to this: 
 ```
 Received a request at Mon Aug 1 14:11:42 at xxxxx.ngrok.io from intuit_notification_server/1.0
Body of the request was :
stdClass Object
(
    [eventNotifications] => Array
        (
            [0] => stdClass Object
                (
                    [realmId] => 193514292359317
                    [dataChangeEvent] => stdClass Object
                        (
                            [entities] => Array
                                (
                                    [0] => stdClass Object
                                        (
                                            [name] => Payment
                                            [id] => 178
                                            [operation] => Create
                                            [lastUpdated] => 2016-08-01T21:07:08.000Z
                                        )

                                )

                        )

                )

        )

)

Request is verified
```

If it doesn't work for you, submit an issue and I can try to debug the issue with you, or contact me at tristan_sokol@intuit.com