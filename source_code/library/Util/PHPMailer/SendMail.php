<?php
if (!defined('BASE_PATH')) exit('Access Denied!');
/**
 * 发送邮件类
 * @author lichanghua
 */
class Util_PHPMailer_SendMail {
    
    static public function postEmail ($to, $subject = '',$body = '' ) {
        header('Content-Type:text/html;Charset=utf-8');
        $smtp_config = Common::getConfig('smtpConfig');
        $mail = new Util_PHPMailer_PHPMailer();
        $mail->CharSet ="UTF-8";                                           //设定邮件编码，默认ISO-8859-1，如果发中文此项必须设置，否则乱码
        $mail->IsSMTP();                                                   // 设定使用SMTP服务
        $mail->SMTPAuth   = true;                                          // 启用 SMTP 验证功能
        $mail->Host       =  $smtp_config['mailhost'];                     // SMTP 服务器
        $mail->Port       =  $smtp_config['mailport'];                     // SMTP服务器的端口号
        $mail->Username   =  $smtp_config['companymail'];                  // SMTP服务器用户名
        $mail->Password   =  $smtp_config['mailpasswd'];                   // SMTP服务器密码
        $mail->SetFrom($smtp_config['companymail'], $smtp_config['mailauthor']);
        //$mail->SMTPDebug = true;                                         //调试模式
        $mail->AddAddress($to, '');
        $mail->WordWrap   = 50;                                            // 设置自动换行50个字符
        $mail->isHTML(true);
        $mail->Subject    = $subject;
        $mail->AltBody    = 'To view the message, please use an HTML compatible email viewer!';
        $mail->Body       = $body;
        if(!$mail->Send()) {
            return  'Mailer Error: ' . $mail->ErrorInfo;
        } else {
            return true;
        }
    }
    
}