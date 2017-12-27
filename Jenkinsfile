// Declarative //
pipeline {
    agent {
            label 'slave-php'
        }
    stages {
        stage('Build') {
            steps {
                sh 'cp app/config/parameters_$BRANCH_NAME.yml.dist app/config/parameters.yml'
            }
        }

        stage('Test') {
            steps {
                sh 'php app/console cache:clear --env=prod'
                sh 'php app/console cache:clear --env=dev'
                sh 'chmod -R 777 app/cache/ app/logs/'
                sh 'php app/console doc:mig:mig -q'
                sh 'rm -rf app/cache/*  app/logs/*'
            }
        }

        stage('Deploy') {
            steps {
                sh 'sudo docker build -t registry-internal.cn-shanghai.aliyuncs.com/sandbox3/rest:$BRANCH_NAME .'
                sh 'sudo docker login -u account@sandbox3.cn -p Sandhill2290 registry-internal.cn-shanghai.aliyuncs.com'
                sh 'sudo docker push registry-internal.cn-shanghai.aliyuncs.com/sandbox3/rest'

                script {
                    if (env.BRANCH_NAME == 'develop') {
                        sh "curl 'https://cs.console.aliyun.com/hook/trigger?triggerUrl=Y2RlY2RkMTJlYTZhOTRmNTQ5MDQ3MWFjODJiMjI5MjNifGFwaS1yZXN0fHJlZGVwbG95fDE5dWRia2hodTMyMXF8&secret=6d4d634e564b4732754d7444666a783428ec517daa50772b9f8b4280c7a19c91'"
                        sh "curl 'https://cs.console.aliyun.com/hook/trigger?triggerUrl=Y2RlY2RkMTJlYTZhOTRmNTQ5MDQ3MWFjODJiMjI5MjNifGFwaS1yZXN0LWNyb250YWJ8cmVkZXBsb3l8MTl1ZGJwNW5sNGo5Ynw=&secret=78485350324763427573466f42356c67900a9191a9e43c6d40ee06287766e6bd'"
                    } else if (env.BRANCH_NAME == 'master') {
                        sh "curl 'https://cs.console.aliyun.com/hook/trigger?triggerUrl=Y2YxOTJlM2JlYzM0YjRmMjI4ZDVlNzI2MGVmM2MwMjExfGFwaS1yZXN0fHJlZGVwbG95fDE5cTVqN2dyMzhpMXJ8&secret=4951497a6843474c6c475468304b5150e0d081d68689da5a441b622bc4cb2a12'"
                        sh "curl 'https://cs.console.aliyun.com/hook/trigger?triggerUrl=Y2YxOTJlM2JlYzM0YjRmMjI4ZDVlNzI2MGVmM2MwMjExfGFwaS1yZXN0LWNyb250YWJ8cmVkZXBsb3l8MTl2dTRqNmxhYm5yOXw=&secret=5736514358755531716b447a30426f6facbc0417668deaf30623c219fb8692fe'"
                    } else if (env.BRANCH_NAME == 'release_production') {
                        sh "curl 'https://cs.console.aliyun.com/hook/trigger?triggerUrl=YzZjYjc4M2Y5ZTkxMTRhYjE4MmU2MjNhZmM2ZGE3YWJmfGFwaS1yZXN0fHJlZGVwbG95fDFhMDNhdDd2ZGU3cmV8&secret=5346636c484a5561556c4f6b4f4d696c5bf3203772ebce62b625830985ba9d96'"
                        sh "curl 'https://cs.console.aliyun.com/hook/trigger?triggerUrl=YzZjYjc4M2Y5ZTkxMTRhYjE4MmU2MjNhZmM2ZGE3YWJmfGFwaS1yZXN0LWNyb250YWJ8cmVkZXBsb3l8MWEwM2F0dGxsdnBodHw=&secret=4d3779756851617876796744784b54386ab8064825a1a6c418ab58573e6f5d32'"
                    }else if (env.BRANCH_NAME == 'demo') {
                        sh "curl 'https://cs.console.aliyun.com/hook/trigger?triggerUrl=YzZjYjc4M2Y5ZTkxMTRhYjE4MmU2MjNhZmM2ZGE3YWJmfGRlbW8tYXBpLXJlc3R8cmVkZXBsb3l8MWEwM2F1cW5pZzg3Mnw=&secret=333061644936674c6d58436c7159744506c54a1d944fc0ed14897271a88f4994'"
                        sh "curl 'https://cs.console.aliyun.com/hook/trigger?triggerUrl=YzZjYjc4M2Y5ZTkxMTRhYjE4MmU2MjNhZmM2ZGE3YWJmfGRlbW8tYXBpLXJlc3QtY3JvbnRhYnxyZWRlcGxveXwxYTAzYjAwamR0dmkzfA==&secret=78396568754e696a6c4d75747a374b571701c9c15bc038e04038c43cdbcf7eca'"
                    } else {
                        echo 'I execute elsewhere'
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

