// Declarative //
pipeline {
    agent {
            label 'slave-php'
        }
    stages {
        stage('Notice') {
            steps {
                sh \"curl 'https://oapi.dingtalk.com/robot/send?access_token=99d023c34e9dc10ce131a60715cb44d34d4ded3b9e61fec5f2534576b4cd9370' \
                       -H 'Content-Type: application/json' \
                       -d ' {"msgtype": "text", "text": { "content": "构建完成" } }' \"
            }
        }
    }
}
