# How to run custom Lambda runtime for PHP?
Follow this step by step guide to use Lambda custom runtime for running PHP code on AWS Lambda.

1. Launch an EC2 instance [amzn-ami-hvm-2018.03.0.20181129-x86_64-gp2](https://docs.aws.amazon.com/lambda/latest/dg/lambda-runtimes.html)
1. SSH to the instance and install packages by running following commands
    ```
    sudo yum update -y
    sudo yum install autoconf bison gcc gcc-c++ openssl-devel libcurl-devel libxml2-devel libjpeg-devel libpng-devel git -y
    ```
1. Compiling PHP from source
    1. Download PHP 7.3 source
        ```
        mkdir ~/php-7.3-bin
        curl -sL https://github.com/php/php-src/archive/php-7.3.0.tar.gz | tar -xvz
        cd php-src-php-7.3.0
        ```
    1. Compiling
        ```
        ./buildconf --force
        ./configure --prefix=/home/ec2-user/php-7.3-bin/ --with-openssl --with-openssl-dir=/usr/include/openssl --with-curl --with-zlib --with-gd --with-jpeg-dir=/usr/lib64
        make install
        ```
1. Clone repository `cd ~; git clone https://github.com/kgruszowski/lambda-custom-runtime-php.git`
1. Copy PHP interpreter `cp ~/php-7.3-bin/bin/php ~/lambda-custom-runtime-php/bin`
1. Enter to working directory
1. Install Composer
    ```
    curl -sS http://getcomposer.org/installer | ./bin/php
    ```
1. Install dependencies
    ```
    ./bin/php composer.phar install
    ```
1. Make zip files necessary to deploy code
    ```
    zip -r runtime.zip bin/ bootstrap
    zip -r vendor.zip vendor/
    zip add_watermark.zip src/add_watermark.php
    ```
1. Create IAM role and attach **AWSLambdaBasicExecutionRole** policy
1. Configure [AWS CLI tool](https://docs.aws.amazon.com/cli/latest/userguide/cli-chap-configure.html) 
1. Prepare layers (make note about ARN of every layer that you published, it will be needed to create lambda function)
    1. Custom runtime layer
        ```
        aws lambda publish-layer-version --layer-name php-custom-runtime --zip-file fileb://runtime.zip
        ```
    1. Layer with vendor files
        ```
        aws lambda publish-layer-version --layer-name php-custom-runtime-vendor --zip-file fileb://vendor.zip
        ```
1. Create function
    ```
    aws lambda create-function \
    --function-name watermark-php \
    --zip-file fileb://watermark.zip \
    --handler watermark \
    --role "{ARN_OF_CREATED_ROLE}" \
    --runtime provided \
    --layers "{ARN_OF_RUNTIME_LAYER}" "{ARN_OF_VENDOR_LAYER}"
    ```
1. In Lambda Configuration Designer set environment variables **public_key** and **secret_key** ([access key documentation](https://docs.aws.amazon.com/general/latest/gr/aws-sec-cred-types.html#access-keys-and-secret-access-keys)) 
1. Create an S3 bucket with two folders inside (origin, watermark)
1. Create an S3 Trigger in Lambda Configuration Designer
    * in Designer sidebar choose **S3**
    * choose bucket you've created
    * choose *All object create events* in **Event type** selector
    * in **Prefix** field set *origin/* and click **Add** button
1. Test function
    1. Upload a jpg image to *{YOUR_BUCKET}/origin*
    1. See result image in *{YOUR_BUCKET}/watermark*
    1. Examine CloudWatch logs for Lambda function
        