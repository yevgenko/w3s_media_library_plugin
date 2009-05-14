<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/2000/REC-xhtml1-200000126/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
  <head>
  </head>
  <body>
    <script type="text/javascript" src="/w3sCmsPlugin/js/scriptaculous/lib/js/prototype.js"></script>
    <?php use_helper('Javascript', 'I18N') ?>
    <?php
     //Get the path to tinyMCE
     $tinyMCEPath=sfConfig::get('sf_rich_text_js_dir');
     //Check if its available
     if ($tinyMCEPath)
     {
       //Appenda  slash in the front
       $tinyMCEPath="/".$tinyMCEPath;
       //Append the javascript file name
       $tinyMCEPath.="/tiny_mce_popup.js";
     }
     else
     {
       //Assume default path
       $tinyMCEPath='/sf/tinymce/js/tiny_mce_popup.js';
     }
     echo javascript_include_tag($tinyMCEPath);
     
    ?>
    <?php echo javascript_tag("
    function setFileSrc(src)
    {
      if (opener)
      {
        opener.document.getElementById('w3s_image_preview').innerHTML = '<img src=\"'+src+'\" \/>';
        opener.document.getElementById('w3s_ppt_image').value = src;
        opener.document.getElementById('w3s_ppt_width').value = '';
        opener.document.getElementById('w3s_ppt_height').value = '';
        opener.document.getElementById('w3s_ppt_size').value = '';
        opener.document.getElementById('w3s_overlay').style.display = 'none';
        window.close();
      }
      else
      {
        if (tinyMCEPopup)
        {
          var mediaLibrary=  tinyMCEPopup.getWindowArg(\"mediaLibrary\");
          mediaLibrary.fileBrowserReturn(src, tinyMCEPopup);
        }
        else
        {
          opener.tinyMCEPopup.fileBrowserReturn(src,null);
          window.close();
        }
      }
    }
    function windowClose()
    {
      if (opener)
      {
        opener.document.getElementById('w3s_overlay').style.display = 'none';
      }
    }
    Event.observe(window, 'unload', windowClose, false);
    ") ?>
    <div id="sf_asset_container">
      <h1><?php echo __('Media library (%1%)', array('%1%' => $current_dir_slash), 'sfMediaLibrary') ?></h1>
      <div id="sf_asset_content">
        <div id="sf_asset_controls">

          <?php echo form_tag('sfMediaLibrary/upload', 'class=float-left id=sf_asset_upload_form name=sf_asset_upload_form multipart=true') ?>
          <?php echo input_hidden_tag('current_dir', $currentDir) ?>
          <?php echo input_hidden_tag('mode', 'choice') ?>
            <fieldset>
              <div class="form-row">
                <?php echo label_for('file', __('Add a file:', array(), 'sfMediaLibrary'), '') ?>
                <div class="content"><?php echo input_file_tag('file') ?></div>
              </div>
            </fieldset>
            <ul class="sf_asset_actions">
             <li><?php echo submit_tag(__('Add', array(), 'sfMediaLibrary'), array (
                'name' => 'add',
                'class' => 'sf_asset_action_add_file',
                'onclick' => "if($('file').value=='') { alert('".__('Please choose a file first', array(), 'sfMediaLibrary')."');return false; }",
              )) ?></li>
            </ul>
            </form>

            <?php echo form_tag('sfMediaLibrary/mkdir', 'class=float-left id=sf_asset_mkdir_form name=sf_asset_mkdir_form') ?>
            <?php echo input_hidden_tag('current_dir', $currentDir) ?>
            <?php echo input_hidden_tag('mode', 'choice') ?>
            <fieldset>
              <div class="form-row">
                <?php echo label_for('dir', __('Create a dir:', array(), 'sfMediaLibrary'), '') ?>
                <div class="content"><?php echo input_tag('name', null, 'size=15 id=dir') ?></div>
              </div>
            </fieldset>
            <ul class="sf_asset_actions">
              <li><?php echo submit_tag(__('Create', array(), 'sfMediaLibrary'), array (
                'name' => 'create',
                'class' => 'sf_asset_action_add_folder',
                'onclick' => "if($('dir').value=='') { alert('".__('Please enter a directory name first', array(), 'sfMediaLibrary')."');return false; }",
              )) ?></li>
            </ul>
          </form>

        </div>

        <br clear="both" /><br clear="both" />
      <div id="sf_asset_content_popup">
        <?php include_partial('sfMediaLibrary/dirs', array('dirs' => $dirs, 'currentDir' => $currentDir, 'parentDir' => $parentDir, 'is_file' => (count($files) > 0))) ?>
        <?php include_partial('sfMediaLibrary/files', array('files' => $files, 'currentDir' => $currentDir, 'webAbsCurrentDir' => $webAbsCurrentDir, 'count' => count($dirs))) ?>
      </div>
    </div>
  </body>
</html>