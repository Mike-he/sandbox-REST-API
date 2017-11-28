// Declarative //
pipeline {
    agent {
            label 'slave-php'
        }
    stages {
        stage('Build') {
            steps {
                script {
                    if (env.BRANCH_NAME == 'develop') {
                            sh 'sudo docker build -t registry-internal.cn-shanghai.aliyuncs.com/sandbox3/rest:dev .'
                    } else if (env.BRANCH_NAME == 'master') {
                            echo 'master'
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

            }
        }


    }
}
