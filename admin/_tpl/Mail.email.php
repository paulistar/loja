<?php

if (!file_exists(__DIR__ . "/../dashboard.json")):
    $LicenseCheckMail = "<p style='font-size: 0.8rem; color: red;'>ATENÇÃO: Esse versão do Work Control® não foi licenciada. Utilizar esse software sem licença é crime. Entre em contato via cursos@upinside.com.br para maiores esclarecimentos!</p>";
else:
    $LicenseCheckMail = null;
endif;

$MailContent = '
<table width="550" style="font-family: "Trebuchet MS", sans-serif;">
 <tr><td>
  <font face="Trebuchet MS" size="3">
   #mail_body#
  </font>
  <p style="font-size: 0.875em;">
  <img src="' . BASE . '/admin/_img/mail.jpg" alt="Atenciosamente ' . ADMIN_NAME . '" title="Atenciosamente ' . ADMIN_NAME . '" /><br><br>
   ' . AGENCY_CONTACT . '<br>Telefone: ' . AGENCY_PHONE . '<br>E-mail: ' . AGENCY_EMAIL . '<br><br>
   <a title="' . AGENCY_NAME . '" href="' . AGENCY_URL . '">' . AGENCY_NAME . '</a><br>' . AGENCY_ADDR . '<br>'
        . AGENCY_CITY . '/' . AGENCY_UF . ' - ' . AGENCY_ZIP . '<br>' . AGENCY_COUNTRY . '
  </p>
  ' . $LicenseCheckMail . '
  </td></tr>
</table>
<style>body, img{max-width: 550px !important; height: auto !important;} p{margin-botton: 15px 0 !important;}</style>';
