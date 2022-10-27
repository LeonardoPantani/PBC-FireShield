<!DOCTYPE html>
<?php
$droot = $_SERVER['DOCUMENT_ROOT'];
include "$droot/Config/config.php";
include "$droot/Functions/funzioni.php";
include "$droot/Functions/mobile.php";
include "$droot/Functions/langHandler.php";

if (isset($_SERVER["REDIRECT_STATUS"])) {
    $stato = $_SERVER["REDIRECT_STATUS"];
    $errori = array(
		400 => array("400", _("La richiesta inviata ha una sintassi non valida.")),
        401 => array("401", _("Il server ti ha impedito di accedere a questa risorsa.")),
        403 => array("403", _("Il server ha rifiutato di rispondere alla tua richiesta.")),
        404 => array("404", _("La pagina che stavi cercando non esiste o è stata rimossa.")),
        405 => array("405", _("Il metodo specificato nella linea di richiesta non autorizzato per la risorsa specificata.")),
        408 => array("408", _("Il browser ha fallito l'invio della richiesta nel tempo autorizzato dal server.")),
        500 => array("500", _("La richiesta è fallita a causa di una condizione inaspettata incontrata dal server.")),
        501 => array("501", _("Il server non supporta la funzionalità necessaria per rispondere alla richiesta.")),
		502 => array("502", _("Il server ha ricevuto una risposta non valida dal server di upstream mentre cercava di rispondere alla tua richiesta.")),
		503 => array("503", _("Il server non è riuscito a gestire la richiesta a causa di un errore.")),
        504 => array("504", _("Il server di upstream ha fallito l'invio della richiesta nel tempo autorizzato dal server.")),
    );
    $titolo = $errori[$stato][0];
    $messaggio = $errori[$stato][1];
} else {
    $titolo = "";
}
if ($titolo == false || strlen($stato) != 3) {
	$titolo = "500";
	$messaggio = _("La richiesta è fallita a causa di una condizione inaspettata incontrata dal server.");
}
?>
<html lang="<?php if(isset($lang)){echo substr($lang, 0, 2);}else{echo 'it';}?>"> <!-- Copyright FireShield. All rights reserved. -->
<head>
    <title><?php echo $titolo." - ".$solutionname; ?></title>
	<?php include "$droot/Functions/Include/head_main.php";?>
    <?php include "$droot/Functions/Include/analytics.php";?>
</head>
<style>
  * {
  -moz-box-sizing:border-box;
  -webkit-box-sizing:border-box;
  box-sizing:border-box;
  }
  html, body, div, span, object, iframe, h1, h2, h3, h4, h5, h6, p, blockquote, pre,
  abbr, address, cite, code, del, dfn, em, img, ins, kbd, q, samp,
  small, strong, sub, sup, var, b, i, dl, dt, dd, ol, ul, li,
  fieldset, form, label, legend, caption, article, aside, canvas, details, figcaption, figure,  footer, header, hgroup,
  menu, nav, section, summary, time, mark, audio, video {
  margin:0;
  padding:0;
  border:0;
  outline:0;
  vertical-align:baseline;
  background:transparent;
  }
  article, aside, details, figcaption, figure, footer, header, hgroup, nav, section {
  display: block;
  }
  html {
  font-size: 16px;
  line-height: 24px;
  width:100%;
  height:100%;
  -webkit-text-size-adjust: 100%;
  -ms-text-size-adjust: 100%;
  overflow-y:scroll;
  overflow-x:hidden;
  }
  img {
  vertical-align:middle;
  max-width: 100%;
  height: auto;
  border: 0;
  -ms-interpolation-mode: bicubic;
  }
  body {
  min-height:100%;
  -webkit-font-smoothing: subpixel-antialiased;
  }
  .clearfix {
  clear:both;
  zoom: 1;
  }
  .clearfix:before, .clearfix:after {
  content: "\0020";
  display: block;
  height: 0;
  visibility: hidden;
  }
  .clearfix:after {
  clear: both;
  }
</style>
<style>
  .plain.error-page-wrapper {
  font-family: 'Source Sans Pro', sans-serif;
  background-color:#6355bc;
  padding:0 5%;
  position:relative;
  }
  .plain.error-page-wrapper .content-container {
  -webkit-transition: left .5s ease-out, opacity .5s ease-out;
  -moz-transition: left .5s ease-out, opacity .5s ease-out;
  -ms-transition: left .5s ease-out, opacity .5s ease-out;
  -o-transition: left .5s ease-out, opacity .5s ease-out;
  transition: left .5s ease-out, opacity .5s ease-out;
  max-width:400px;
  position:relative;
  left:-30px;
  opacity:0;
  }
  .plain.error-page-wrapper .content-container.in {
  left: 0px;
  opacity:1;
  }
  .plain.error-page-wrapper .head-line {
  transition: color .2s linear;
  font-size:48px;
  line-height:60px;
  color:rgba(255,255,255,.2);
  letter-spacing: -1px;
  margin-bottom: 5px;
  }
  .plain.error-page-wrapper .subheader {
  transition: color .2s linear;
  font-size:36px;
  line-height:46px;
  color:#fff;
  }
  .plain.error-page-wrapper hr {
  height:1px;
  background-color: rgba(255,255,255,.2);
  border:none;
  width:250px;
  margin:35px 0;
  }
  .plain.error-page-wrapper .context {
  transition: color .2s linear;
  font-size:18px;
  line-height:27px;
  color:#fff;
  }
  .plain.error-page-wrapper .context p {
  margin:0;
  }
  .plain.error-page-wrapper .context p:nth-child(n+2) {
  margin-top:12px;
  }
  .plain.error-page-wrapper .buttons-container {
  margin-top: 45px;
  overflow: hidden;
  }
  .plain.error-page-wrapper .buttons-container a {
  transition: color .2s linear, border-color .2s linear;
  font-size:14px;
  text-transform: uppercase;
  text-decoration: none;
  color:#fff;
  border:2px solid white;
  border-radius: 99px;
  padding:8px 30px 9px;
  display: inline-block;
  float:left;
  }
  .plain.error-page-wrapper .buttons-container a:hover {
  background-color:rgba(255,255,255,.05);
  }
  .plain.error-page-wrapper .buttons-container a:first-child {
  margin-right:25px;
  }
  @media screen and (max-width: 485px) {
  .plain.error-page-wrapper .header {
  font-size:36px;
  }
  .plain.error-page-wrapper .subheader {
  font-size:27px;
  line-height:38px;
  }
  .plain.error-page-wrapper hr {
  width:185px;
  margin:25px 0;
  }
  .plain.error-page-wrapper .context {
  font-size:16px;
  line-height: 24px;
  }
  .plain.error-page-wrapper .buttons-container {
  margin-top:35px;
  }
  .plain.error-page-wrapper .buttons-container a {
  font-size:13px;
  padding:8px 0 7px;
  width:45%;
  text-align: center;
  }
  .plain.error-page-wrapper .buttons-container a:first-child {
  margin-right:10%;
  }
  }
</style>
<style>
  .background-color {
  background-color: rgba(51, 51, 51, 1) !important;
  }
  .primary-text-color {
  color: #FFFFFF !important;
  }
  .secondary-text-color {
  color: rgba(150, 139, 217, 1) !important;
  }
  .border-button {
  color: #FFFFFF !important;
  border-color: #FFFFFF !important;
  }
  .button {
  background-color: #FFFFFF !important;
  color:  !important;
  }
</style>
<body class="plain error-page-wrapper background-color background-image">
  <div class="content-container">
      <div class="head-line secondary-text-color">
        <?php echo $titolo; ?>
      </div>
      <div class="subheader primary-text-color">
        <?php echo $messaggio; ?>
      </div>
      <hr>
      <div class="clearfix"></div>
      <div class="context primary-text-color">
        <p>
        <br><?php
			echo _("Puoi tornare alla Homepage ora. Se pensi che questo sia un errore, inviaci una mail cliccando il tasto sottostante, grazie!");
			?>
        </p>
      </div>
      <div class="buttons-container">
        <a class="border-button" href="/" target="_self"><?php echo _("Torna alla HomePage"); ?></a>
        <br><br>
        <a class="border-button" href="mailto:<?php echo $feedbackmail; ?>" target="_self"><?php echo _("Segnala un problema"); ?></a>
		<br><br><br>
		<a class="border-button" href="/Translations/setLanguage.php?l=<?php if($lang == "en_US") { echo 'it_IT'; } elseif($lang == "it_IT") { echo 'en_US'; } else { echo 'ERROR'; } ?>" target="_self"><small><?php if($lang == "en_US") { echo 'Cambia lingua in ITALIANO'; } elseif($lang == "it_IT") { echo 'Change language into ENGLISH'; } else { echo 'ERROR'; } ?></small></a>
      </div>
  </div>
  <script>
  document.getElementById('jquery').addEventListener('load', function() {	
      function ErrorPage(container, pageType, templateName) {
        this.$container = $(container);
        this.$contentContainer = this.$container.find(templateName == 'sign' ? '.sign-container' : '.content-container');
        this.pageType = pageType;
        this.templateName = templateName;
      }

      ErrorPage.prototype.centerContent = function () {
        var containerHeight = this.$container.outerHeight()
          , contentContainerHeight = this.$contentContainer.outerHeight()
          , top = (containerHeight - contentContainerHeight) / 2
          , offset = this.templateName == 'sign' ? -100 : 0;

        this.$contentContainer.css('top', top + offset);
      };

      ErrorPage.prototype.initialize = function () {
        var self = this;

        this.centerContent();
        this.$container.on('resize', function (e) {
          e.preventDefault();
          e.stopPropagation();
          self.centerContent();
        });

        // fades in content on the plain template
        if (this.templateName == 'plain') {
          window.setTimeout(function () {
            self.$contentContainer.addClass('in');
          }, 500);
        }

        // swings sign in on the sign template
        if (this.templateName == 'sign') {
          $('.sign-container').animate({textIndent : 0}, {
            step : function (now) {
              $(this).css({
                transform : 'rotate(' + now + 'deg)',
                'transform-origin' : 'top center'
              });
            },
            duration : 1000,
            easing : 'easeOutBounce'
          });
        }
      };


      ErrorPage.prototype.createTimeRangeTag = function(start, end) {
        return (
          '<time utime=' + start + ' simple_format="DD MMM, YYYY HH:mm">' + start + '</time> - <time utime=' + end + ' simple_format="DD MMM, YYYY HH:mm">' + end + '</time>.'
        )
      };


      ErrorPage.prototype.handleStatusFetchSuccess = function (pageType, data) {
        if (pageType == '503') {
          $('#replace-with-fetched-data').html(data.status.description);
        } else {
          if (!!data.scheduled_maintenances.length) {
            var maint = data.scheduled_maintenances[0];
            $('#replace-with-fetched-data').html(this.createTimeRangeTag(maint.scheduled_for, maint.scheduled_until));
            $.fn.localizeTime();
          }
          else {
            $('#replace-with-fetched-data').html('<em><?php echo _("(Non ci sono manutenzioni previste)"); ?></em>');
          }
        }
      };


      ErrorPage.prototype.handleStatusFetchFail = function (pageType) {
        $('#replace-with-fetched-data').html('<em><?php echo _("(Inserire un url con stato pagina corretto)"); ?></em>');
      };


      ErrorPage.prototype.fetchStatus = function (pageUrl, pageType) {
        if (!pageUrl || !pageType || pageType == '404') return;

        var url = ''
          , self = this;

        if (pageType == '503') {
          url = pageUrl + '/api/v2/status.json';
        }
        else {
          url = pageUrl + '/api/v2/scheduled-maintenances/active.json';
        }

        $.ajax({
          type : "GET",
          url : url,
        }).success(function (data, status) {
          self.handleStatusFetchSuccess(pageType, data);
        }).fail(function (xhr, msg) {
          self.handleStatusFetchFail(pageType);
        });

      };
      var ep = new ErrorPage('body', "404", "plain");
      ep.initialize();

      // hack to make sure content stays centered >_<
      $(window).on('resize', function() {
        $('body').trigger('resize')
      });
  });
  </script>
</body>
</html>