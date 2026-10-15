
pipeline {
    agent any

    environment {
        IMAGE_NAME = 'andikacatering-api-main-jenkins'
        REPORT_FILE = 'grype-report.json'
    }

    stages {
        stage('Checkout') {
            steps {
                checkout scm
            }
        }

        stage('Build Docker Image') {
            steps {
                sh '''
                    docker build -t ${IMAGE_NAME}:${BUILD_NUMBER} .
                '''
            }
        }

        stage('Scan with Grype') {
            steps {
                script {
                    int exitCode = sh(
                        script: '''
                            grype ${IMAGE_NAME}:${BUILD_NUMBER} \
                                -o json > ${REPORT_FILE}
                        ''',
                        returnStatus: true
                    )

                    env.GRYPE_EXIT = "${exitCode}"

                    if (exitCode != 0) {
                        currentBuild.result = 'FAILURE'
                        error('Grype scan failed. Check the report.')
                    }
                }
            }
        }
    }

    post {
        always {
            script {
                if (fileExists(env.REPORT_FILE)) {
                    archiveArtifacts(
                        artifacts: env.REPORT_FILE,
                        allowEmptyArchive: true
                    )

                    withCredentials([
                        string(
                            credentialsId: 'dc-webhook',
                            variable: 'DISCORD_WEBHOOK'
                        )
                    ]) {
                        sh '''
                            MESSAGE="Grype scan selesai untuk \
${IMAGE_NAME}:${BUILD_NUMBER}"

                            jq -n --arg content "$MESSAGE" \
                                '{content: $content}' > payload.json

                            curl -sS -X POST \
                                -H 'Content-Type: application/json' \
                                --data @payload.json \
                                "$DISCORD_WEBHOOK"
                        '''
                    }
                } else {
                    echo 'Laporan Grype tidak ditemukan.'
                }
            }
        }
    }
}
