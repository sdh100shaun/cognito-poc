# AWS Cognito poc application
This sample application shows some basic functionality written against [AWS Cognito](https://aws.amazon.com/cognito). The following functionality is covered;

* User registration
* User login
* Accessing a secured page if logged in
* Resetting a forgotten password
* Logout

The application is written in PHP. This is only a POC 

The steps to get started are divided in two sections;

1. [Set up AWS Cognito with the correct configuration](#set-up-aws-cognito-with-the-correct-configuration)

## Set up AWS Cognito with the correct configuration
First we will set up a new AWS Cognito user pool with the correct configuration.

1. Visit your AWS console and go to the AWS Cognito service. Click on "Manage your User Pools" and click "Create a User Pool".
2. Specify a name for your pool and click "Review Defaults".
3. Optional: edit the password policy to remove complexity requirements. If you are just testing, using simple passwords will make it easier.
4. Click the "edit client" link. Specify a name for your app and be sure to *disable* the client secret and *enable* the ADMIN_NO_SRP_AUTH option.
5. Click "Create pool". Take note of the *Pool Id* at the top of the page and click on the apps page. Here, take note of the *App client id*.
6. Create a new file called `.env` next to the Dockerfile. Add the AWS region you are using, the pool ID and the client ID to this file. For the proper format, see below.
7. There are two methods for setting up the required AWS credentials for communicating with the AWS CLI:
1. The recommended way is to spin up an EC2 instance with a role. You then assign the correct permissions to this role.
2. If you want to spin up the application outside of AWS, you will need an AWS user. Create an AWS User and get the access token and secret key. Add these to the .env file (see below).
8. For testing, you can attach the `AmazonCognitoPowerUser` policy to either the created role or the user.

That should be it! The format is the .env file is as follows:

```
REGION=eu-west-1
CLIENT_ID=eu-west-1_abc123
USERPOOL_ID=abc123