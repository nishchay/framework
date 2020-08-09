<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8">
        <title>Exception Occurred - <?= $type ?></title>
        <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Hind:300">
        <style type="text/css">
            body{font-family: Hind, monospace;font-size: 16px;line-height: 22px;font-weight: 300}
            .error-header{padding: 5px 0 5px 45px;background-color: #FFF;border-bottom: 1px solid #D1CACA;color: #fe10c8;font-family: Hind,verdana;background-color: #F8F8F8;font-size: 20px;line-height: 25px;}
            .error-header strong{font-weight: bold;border-right: 1px solid #fe10c8;padding-right: 10px}
            .line-container{box-shadow: 5px 0 5px -5px #D7D4D4;float: left;width: 40px;}
            .line-number{padding:2px 4px 2px 0;color: #b0b0b0;text-align: right;}
            .error-line-number{color:#fff;font-weight:bold;background-color: #fe10c8}
            .code-container{margin-left:40px;}
            .code-div{padding:2px;color:#b0b0b0;height: 22px;overflow: hidden;max-width: 100%;padding-left: 15px;}
            .code-div:last-child{border-bottom: 0}
            .error-div{color:#000;background-color: #ffeafb}
            .file-line{padding: 5px 0 5px 45px;background-color: #FFF;color: #fe10c8;font-family: Hind,verdana;border-top: 1px solid #D1CACA;background-color: #F8F8F8;}
            .trace-div{margin-top: 10px;}
            .trace-line{border-bottom:1px solid #cccccc;background-color: #f6f6f6;padding: 10px 5px}
            .trace-line:nth-child(odd){background-color: #FFF}
            .trace-line small{color: #9B9B9B}
        </style>
    </head>
    <body>
        <div class="error-header">
            <?php if ($code > 0) { ?>
                (<?= $code ?>)
            <?php } ?>
            <strong><?= $type ?></strong>
            <?= $errorString ?>
        </div>
        <?php
        if ($line !== 0) {
            ?>
            <div class="line-container">
                <?php
                $start = $line - 6;
                $end = $line + 5;
                $code = [];
                foreach (file($file) as $lineNumber => $value) {
                    $isErrorLine = ($lineNumber == ($line - 1));
                    $toShow = ($lineNumber >= $start && $lineNumber <= $end);
                    if ($toShow) {
                        $value = str_replace(' ', '&nbsp;', htmlspecialchars($value)) . '&nbsp;';
                        $code[] = [
                            $isErrorLine ? 'error-div' : '',
                            rtrim($value)
                        ];
                        ?>
                        <div class="line-number <?= $isErrorLine ? 'error-line-number' : '' ?>">
                            <?= $lineNumber + 1 ?>
                        </div>
                        <?php
                    }
                    if ($lineNumber == $end) {
                        break;
                    }
                }
                ?>
            </div>
            <div class="code-container">
                <?php
                foreach ($code as $codeLine) {
                    ?>
                    <div class="code-div <?= $codeLine[0] ?>"><span><?= $codeLine[1] ?></span></div>
                    <?php
                }
                ?>
            </div>
            <div style="clear: both"></div>
            <div class="file-line">File:<?= $file ?></div>
            <?php
        }
        ?>
        <div class="trace-div">
            <?php
            foreach ($trace as $row) {
                if (is_string($row)) {
                    continue;
                }
                $fileName = array_key_exists('file', $row) ? $row['file'] : $fileName;
                ?>
                <div class="trace-line">
                    <?php
                    echo (array_key_exists('class', $row) ? ($row['class'] . '->') : '') . $row['function'] . '()';
                    ?><br/>
                    <small><?= (array_key_exists('line', $row) ? ('Line ' . $row['line'] . ' in ') : '') . $fileName ?></small>
                </div>
            <?php } ?>
        </div>
    </body>
</html>
