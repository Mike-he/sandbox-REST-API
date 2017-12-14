// Declarative //
pipeline {
    agent {
            label 'slave-php'
        }
    stages {
        stage('Build') {
            steps {
                echo 'Build Docker Image...'
                script {
                    if (env.BRANCH_NAME == 'develop') {
                            sh 'sudo docker build -t registry-internal.cn-shanghai.aliyuncs.com/sandbox3/rest:dev .'
                    } else if (env.BRANCH_NAME == 'master') {
                            sh 'sudo docker build -t registry-internal.cn-shanghai.aliyuncs.com/sandbox3/rest:staging .'
                    } else {
                        echo 'I execute elsewhere'
                    }
                }
            }
        }

        stage('Test') {
            steps {
                echo 'Testing..'
            }
        }

        stage('Deploy') {
            steps {
                echo 'Deploying..'
                sh 'sudo docker login -u account@sandbox3.cn -p Sandhill2290 registry-internal.cn-shanghai.aliyuncs.com'
                sh 'sudo docker push registry-internal.cn-shanghai.aliyuncs.com/sandbox3/rest'

                script {
                    if (env.BRANCH_NAME == 'develop') {
                        sh "curl 'https://cs.console.aliyun.com/hook/trigger?triggerUrl=Y2RlY2RkMTJlYTZhOTRmNTQ5MDQ3MWFjODJiMjI5MjNifGFwaS1yZXN0fHJlZGVwbG95fDE5dWRia2hodTMyMXF8&secret=6d4d634e564b4732754d7444666a783428ec517daa50772b9f8b4280c7a19c91'"
                        sh "curl 'https://cs.console.aliyun.com/hook/trigger?triggerUrl=Y2RlY2RkMTJlYTZhOTRmNTQ5MDQ3MWFjODJiMjI5MjNifGFwaS1yZXN0LWNyb250YWJ8cmVkZXBsb3l8MTl1ZGJwNW5sNGo5Ynw=&secret=78485350324763427573466f42356c67900a9191a9e43c6d40ee06287766e6bd'"
                    } else if (env.BRANCH_NAME == 'master') {
                        sh "curl 'https://cs.console.aliyun.com/hook/trigger?triggerUrl=Y2YxOTJlM2JlYzM0YjRmMjI4ZDVlNzI2MGVmM2MwMjExfGFwaS1yZXN0fHJlZGVwbG95fDE5cTVqN2dyMzhpMXJ8&secret=4951497a6843474c6c475468304b5150e0d081d68689da5a441b622bc4cb2a12'"
                        sh "curl 'https://cs.console.aliyun.com/hook/trigger?triggerUrl=Y2YxOTJlM2JlYzM0YjRmMjI4ZDVlNzI2MGVmM2MwMjExfGFwaS1yZXN0LWNyb250YWJ8cmVkZXBsb3l8MTl2dTRqNmxhYm5yOXw=&secret=5736514358755531716b447a30426f6facbc0417668deaf30623c219fb8692fe'"
                    } else {
                        echo 'I execute elsewhere'
                    }
                }
            }
        }
        stage('Notice') {
            steps {
                script {
                    if (env.BRANCH_NAME == 'develop') {
                        sh "curl 'https://oapi.dingtalk.com/robot/send?access_token=2cf510246ce6156bee19cfd9071c3af9d346596f21910eb0fc6c3bda2af7bb81' \
                            -H 'Content-Type: application/json' \
                            -d ' { \"msgtype\": \"text\",\"text\": {\"content\": \"REST-dev构建完成\"} }' "
                    } else if (env.BRANCH_NAME == 'master') {
                        sh "curl 'https://oapi.dingtalk.com/robot/send?access_token=2cf510246ce6156bee19cfd9071c3af9d346596f21910eb0fc6c3bda2af7bb81' \
                            -H 'Content-Type: application/json' \
                            -d ' { \"msgtype\": \"text\",\"text\": {\"content\": \"REST-staging构建完成\"} }' "
                    } else {
                        echo 'I execute elsewhere'
                    }
                }
            }
        }
    }
}

