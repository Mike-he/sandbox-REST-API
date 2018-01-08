// Declarative //
pipeline {
    agent {
            label 'slave-php'
        }
    stages {
        stage('Build') {
            steps {
                sh 'cp app/config/parameters_$BRANCH_NAME.yml.dist app/config/parameters.yml'
                sh 'php app/console cache:clear --env=prod'
                sh 'chmod -R 777 app/cache/ app/logs/'
                sh 'php app/console doc:mig:mig -q'
                sh 'rm -rf app/cache/*  app/logs/*'
            }
        }

        stage('Test') {
            steps {

            }
        }

        stage('Deploy') {
            steps {
                sh 'sudo docker build -t registry-internal.cn-shanghai.aliyuncs.com/sandbox3/rest:$BRANCH_NAME .'
                sh 'sudo docker login -u account@sandbox3.cn -p Sandhill2290 registry-internal.cn-shanghai.aliyuncs.com'
                sh 'sudo docker push registry-internal.cn-shanghai.aliyuncs.com/sandbox3/rest'

                script {
                    switch (env.BRANCH_NAME) {
                        case "develop":
                            sh "curl 'https://cs.console.aliyun.com/hook/trigger?triggerUrl=Y2RlY2RkMTJlYTZhOTRmNTQ5MDQ3MWFjODJiMjI5MjNifGFwaS1yZXN0fHJlZGVwbG95fDE5dWRia2hodTMyMXF8&secret=6d4d634e564b4732754d7444666a783428ec517daa50772b9f8b4280c7a19c91'"
                            sh "curl 'https://cs.console.aliyun.com/hook/trigger?triggerUrl=Y2RlY2RkMTJlYTZhOTRmNTQ5MDQ3MWFjODJiMjI5MjNifGFwaS1yZXN0LWNyb250YWJ8cmVkZXBsb3l8MTl1ZGJwNW5sNGo5Ynw=&secret=78485350324763427573466f42356c67900a9191a9e43c6d40ee06287766e6bd'"
                            break;
                    }
                }
            }
        }
    }

    post {
        success {
            script {
                if (env.BRANCH_NAME == 'develop') {
                    sh "curl 'https://oapi.dingtalk.com/robot/send?access_token=2cf510246ce6156bee19cfd9071c3af9d346596f21910eb0fc6c3bda2af7bb81' \
                        -H 'Content-Type: application/json' \
                        -d '{\"actionCard\":{\"title\":\"构建成功【Develop Server】REST-API\",\"text\":\"![screenshot](http://sandbox3-pro-image.oss-cn-shanghai.aliyuncs.com/useless/develop.jpg) \\n#### 构建成功【Develop Server】REST-API\"},\"msgtype\":\"actionCard\"}' "
                } else if (env.BRANCH_NAME == 'master') {
                    sh "curl 'https://oapi.dingtalk.com/robot/send?access_token=2cf510246ce6156bee19cfd9071c3af9d346596f21910eb0fc6c3bda2af7bb81' \
                        -H 'Content-Type: application/json' \
                        -d '{\"actionCard\":{\"title\":\"构建成功【Test Server】REST-API\",\"text\":\"![screenshot](http://sandbox3-pro-image.oss-cn-shanghai.aliyuncs.com/useless/test.jpg) \\n#### 构建成功【Test Server】REST-API\"},\"msgtype\":\"actionCard\"}' "
                } else if (env.BRANCH_NAME == 'release_production') {
                     sh "curl 'https://oapi.dingtalk.com/robot/send?access_token=2cf510246ce6156bee19cfd9071c3af9d346596f21910eb0fc6c3bda2af7bb81' \
                         -H 'Content-Type: application/json' \
                         -d '{\"actionCard\":{\"title\":\"构建成功【Production Server】REST-API\",\"text\":\"![screenshot](http://sandbox3-pro-image.oss-cn-shanghai.aliyuncs.com/useless/product.jpg) \\n#### 构建成功【Production Server】REST-API\"},\"msgtype\":\"actionCard\"}' "
                 } else {
                    echo 'I execute elsewhere'
                }
            }
        }

        failure {
            script {
                sh "curl 'https://oapi.dingtalk.com/robot/send?access_token=2cf510246ce6156bee19cfd9071c3af9d346596f21910eb0fc6c3bda2af7bb81' \
                    -H 'Content-Type: application/json' \
                    -d ' { \"msgtype\": \"text\",\"text\": {\"content\": \"REST-Develop构建失败\"} }' "
            }
        }
    }
}

