<?php
/**
 * Created by PhpStorm.
 * User: site2
 * Date: 07/11/2017
 * Time: 18:08
 */

class Seaway_Experience_PixelController extends Mage_Core_Controller_Front_Action {


    public function indexAction(){

        $insta = $_GET['instagram'];
        if(!empty($insta)){
            Mage::getModel('track/track')->trackLog($insta . ' - pixel' , null);
        }
        // Create an image, 1x1 pixel in size
        $im=imagecreate(1,1);

        // Set the background colour
        $white=imagecolorallocate($im,255,255,255);

        // Allocate the background colour
        imagesetpixel($im,1,1,$white);

        // Set the image type
        header("content-type:image/jpg");

        // Create a JPEG file from the image
        imagejpeg($im);

        // Free memory associated with the image
        imagedestroy($im);

      die;

    }



    public  function sendAction(){





        //use PHPMailer\PHPMailer\PHPMailer;
        //use PHPMailer\PHPMailer\Exception;
        $includePath = Mage::getBaseDir('lib');

        require_once $includePath.DS.'phpmailer'.DS.'src'.DS.'Exception.php';
        require_once $includePath.DS.'phpmailer'.DS.'src'.DS.'PHPMailer.php';
        require_once $includePath.DS.'phpmailer'.DS.'src'.DS.'SMTP.php';



        $subject = "FREE BOARDSHORTS FOR FRIENDS";
        $from    = "seawayexperience@seaway.surf";

        $valores  = array();
        /*$valores  = array(
            array(
                'name'      => 'Bullet Obra',
                'email'     => 'hulakai54@gmail.com',
                'instagram' => 'bulletobra',
                'imagem'    => 'https://seaway.surf/skin/frontend/seaway/modern/images/experience/indicate/20171108/bulletobra.png',
                'link'      => 'https://seaway.surf/indicate/@bulletobra'
            ),
            array(
                'name'      => 'Iris Huddy',
                'email'     => 'iris.mermade@gmail.com',
                'instagram' => 'iriemermade',
                'imagem'    => 'https://seaway.surf/skin/frontend/seaway/modern/images/experience/indicate/20171108/iriemermade.png',
                'link'      => 'https://seaway.surf/indicate/@iriemermade'
            ),
            array(
                'name'      => 'Brad Patterson',
                'email'     => 'big_island_bradley@yahoo.com',
                'instagram' => 'i_brad',
                'imagem'    => 'https://seaway.surf/skin/frontend/seaway/modern/images/experience/indicate/20171108/i_brad.png',
                'link'      => 'https://seaway.surf/indicate/@i_brad'
            ),
            array(
                'name'      => 'Reece Alnas',
                'email'     => 'alnutts4@yahoo.com',
                'instagram' => 'reece.alnas',
                'imagem'    => 'https://seaway.surf/skin/frontend/seaway/modern/images/experience/indicate/20171108/reece.alnas.png',
                'link'      => 'https://seaway.surf/indicate/@reece.alnas'
            ),
            array(
                'name'      => 'Ronald Yamashita',
                'email'     => 'RonaldDKsponge@yahoo.com',
                'instagram' => 'liquid_alchemist',
                'imagem'    => 'https://seaway.surf/skin/frontend/seaway/modern/images/experience/indicate/20171108/liquid_alchemist.png',
                'link'      => 'https://seaway.surf/indicate/@liquid_alchemist'

            )


        );*/

        if(!empty($valores)){

            foreach($valores as $val){


                $name      = $val['name'];
                $email     = $val['email'];
                $instagram = $val['instagram'];


                $html = '<body style="background-color: #FFFFFF;">
                        <img src="'.$val['imagem'].'" style="margin-bottom:10px;" border="0"/>
                        <br/>
                        <br/>
                        <a style="font-size:22px" href="'.$val['link'].'">'.$val['link'].'</a>
                        <br/>
                        <span style="font-size:22px">PS. IMPORTANT: This link only works on mobile.</span>
                        <img src="https://seaway.surf/experience/pixel?instagram='.$instagram.'" style="margin-bottom:10px;" border="0"/>
                     </body>';



                $mail = new PHPMailer(true);                              // Passing `true` enables exceptions
                try {
                    //Server settings
                    $mail->SMTPDebug = 0;                                 // Enable verbose debug output
                    $mail->isSMTP();                                      // Set mailer to use SMTP
                    $mail->Host = 'smtp.seaway.surf';  // Specify main and backup SMTP servers
                    $mail->SMTPAuth = true;                               // Enable SMTP authentication
                    $mail->Username = 'seawayexperience@seaway.surf';                 // SMTP username
                    $mail->Password = 'seaway84';                           // SMTP password
                    //  $mail->SMTPSecure = 'tls';                            // Enable TLS encryption, `ssl` also accepted
                    $mail->Port = 587;                                    // TCP port to connect to

                    //Recipients
                    $mail->setFrom($from, 'Mailer');
                    $mail->addAddress($email, $name);     // Add a recipient
                    //Attachments
                    //Content
                    $mail->isHTML(true);                                  // Set email format to HTML
                    $mail->Subject = $subject;
                    $mail->Body    = $html;
                    $mail->send();
                    echo 'Message has been sent';
                    echo '<br/>';
                } catch (Exception $e) {
                    echo 'Message could not be sent.';
                    echo '<br/>';
                    echo 'Mailer Error: ' . $mail->ErrorInfo;
                    echo '<br/><br/>';
                }

            }

        }







    }



}