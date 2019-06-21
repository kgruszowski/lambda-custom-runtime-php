# How to run custom Lambda runtime for PHP?
Follow this step by step guide to use Lambda custom runtime for running PHP code on AWS Lambda.

1. Launch an EC2 instance [amzn-ami-hvm-2018.03.0.20181129-x86_64-gp2](https://docs.aws.amazon.com/lambda/latest/dg/lambda-runtimes.html)
1. SSH to the instance and install packages by running following commands
    ```
    sudo yum update -y
    sudo yum install autoconf bison gcc gcc-c++ libcurl-devel libxml2-devel libjpeg-devel libpng-devel git -y
    ```
1. Compiling PHP from source
    1. Download PHP 7.3 source
        ```
        mkdir ~/php-7-source
        curl -sL https://github.com/php/php-src/archive/php-7.3.0.tar.gz | tar -xvz
        cd php-src-php-7.3.0
        ```
    1. Compiling
        ```
        ./buildconf --force
        ./configure --prefix=/home/ec2-user/php-7-bin/ --with-openssl-dir=/usr/include/openssl --with-curl --with-zlib --with-gd --with-jpeg-dir=/usr/lib64
        make install
        ```
1. Clone repository `cd ~; git clone https://github.com/kgruszowski/lambda-custom-runtime-php.git`
1. Copy PHP interpreter `cp ~/php-7-bin/bin/php ~/lambda-custom-runtime-php/bin`
1. Make zip files necessary to deploy code
    ```
    zip -r runtime.zip bin/ bootstrap
    zip -r vendor.zip vendor/
    zip watermark.zip src/watermark.php
    ```
1. Configure [AWS CLI tool](https://docs.aws.amazon.com/cli/latest/userguide/cli-chap-configure.html) 
1. Prepare layers
    1. Custom runtime layer
        ```
        aws lambda publish-layer-version --layer-name php-custom-runtime --zip-file fileb://runtime.zip
        ```
    1. Layer with vendor files
        ```
        aws lambda publish-layer-version --layer-name php-custom-runtime-vendor --zip-file fileb://vendor.zip
        ```
        