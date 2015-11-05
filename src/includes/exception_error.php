<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Avenue | Application Error</title>
        <style>
            body {
                font-family: Helvetica, Arial, sans-serif;
                font-size: 14px;
                margin: 24px;
            }
            h1 {
                font-size: 28px;
                color: #4F5B93;
                text-shadow: 1px 0px #FFFFFF;
            }
            h3 {
                text-decoration: underline;
            }
            table {
                width: 100%;
                border-collapse: collapse;
            }
            table tr td:first-child {
                width: 150px;
            }
            table tr td {
                border-bottom: 1px dotted #CCCCCC;
                padding: 12px 24px;
                vertical-align: middle;
            }
            table tr td label {
                font-weight: bold;
            }
            pre {
                font-size: 12px;
                border: 1px solid #CCCCCC;
                background-color: #F1F1F1;
                padding: 12px 24px;
                line-height: 18px;
            }
            footer {
                margin-top: 32px;
                font-weight: normal;
            }
        </style>
    </head>
    <body>
        <section>
            <h1>Oops! Something went wrong in application.</h1>
            <p>The application doesn't work as expected due to the following error:</p>
            <h3>Error Details</h3>
            <?php
                $exceptionClass = $this->getExceptionClass();
                $codeInfo = $this->getCode();
                $messageInfo = $this->getMessage();
                $fileInfo = $this->getFile();
                $lineInfo = $this->getLine();
            ?>
            <table>
                <tbody>
                    <?php if ($codeInfo) {?>
                    <tr>
                        <td><label>Code:</label></td>
                        <td><?php echo $codeInfo;?></td>
                    </tr>
                    <?php }?>
                    <tr>
                        <td><label>Type:</label></td>
                        <td><?php echo $exceptionClass;?></td>
                    </tr>
                    <tr>
                        <td><label>Message:</label></td>
                        <td><?php echo $messageInfo;?></td>
                    </tr>
                    <tr>
                        <td><label>File:</label></td>
                        <td><?php echo $fileInfo;?></td>
                    </tr>
                    <tr>
                        <td><label>Line:</label></td>
                        <td><?php echo $lineInfo;?></td>
                    </tr>
                </tbody>
            </table>
            <h3>Stack Trace</h3>
            <pre><?php echo $this->getTraceAsString(); ?></pre>
        </section>
        <footer>
            <?php echo date('Y'); ?> &copy; Powered by, Avenue Framework - v<?php echo $this->app->version(); ?>
        </footer>
    </body>
</html>