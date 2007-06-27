<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 *
 * @package    symfony
 * @subpackage plugin
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @version    SVN: $Id: actions.class.php 1949 2006-09-05 14:40:20Z fabien $
 */
class BasesfMediaLibraryActions extends sfActions
{
  public function preExecute()
  {
    $this->use_thumbnails = false;
    if(sfConfig::get('app_sfMediaLibrary_use_thumbnails', true) && class_exists('sfThumbnail'))
    {
      $this->use_thumbnails = true;
      $this->thumbnails_dir = sfConfig::get('app_sfMediaLibrary_thumbnails_dir', 'thumbnail');
    } 
  }
  
  public function executeIndex()
  {
    $currentDir = $this->dot2slash($this->getRequestParameter('dir'));
    $this->currentDir = $this->getRequestParameter('dir');
    $this->current_dir_slash = $currentDir . '/';
    $this->webAbsCurrentDir = $this->getRequest()->getRelativeUrlRoot().'/'.sfConfig::get('sf_upload_dir_name').'/assets'.$currentDir;
    $this->absCurrentDir = sfConfig::get('sf_upload_dir').'/assets/'.$currentDir;

    $this->forward404Unless(is_dir($this->absCurrentDir));

    // directories
    $dirsQuery = sfFinder::type('dir')->maxdepth(0)->prune('.*')->discard('.*')->relative();
    if($this->use_thumbnails)
    {
      $dirsQuery = $dirsQuery->discard($this->thumbnails_dir);
    }
    $dirs  = $dirsQuery->in($this->absCurrentDir);
    sort($dirs);
    $this->dirs = $dirs;
    
    // files, with stats
    $files = sfFinder::type('file')->maxdepth(0)->prune('.*')->discard('.*')->relative()->in($this->absCurrentDir);
    sort($files);
    $infos = array();
    foreach ($files as $file)
    {
      $ext  = substr($file, strpos($file, '.') - strlen($file) + 1);
      if (!$this->getRequestParameter('images_only') || $this->isImage($ext))
      {
        $infos[$file] = $this->getInfo($file);
      }
    }
    $this->files = $infos;

    // parent dir
    $tmp = explode(' ', $this->currentDir);
    array_pop($tmp);
    $this->parentDir = implode(' ', $tmp);
  }

  protected function isImage($ext)
  {
    return in_array($ext, array('png', 'jpg', 'gif'));
  }

  public function executeChoice()
  {
    $this->executeIndex();
  }

  public function executeRename()
  {
    $currentDir = $this->dot2slash($this->getRequestParameter('current_path'));
    $this->currentDir = $this->getRequestParameter('current_path');
    $type = $this->getRequestParameter('type');
    $this->count = $this->getRequestParameter('count');
    $this->webAbsCurrentDir = '/'.sfConfig::get('sf_upload_dir_name').'/assets/'.$currentDir;
    $absCurrentDir = sfConfig::get('sf_upload_dir').'/assets/'.$currentDir;
    
    $this->forward404Unless(is_dir($absCurrentDir));

    $name = $this->getRequestParameter('name');
    $new_name = $this->getRequestParameter('new_name');
    if ($type === 'folder')
    {
      $new_name = $this->sanitizeDir($new_name);
      $this->forward404Unless(is_dir($absCurrentDir.'/'.$name));
    }
    else
    {
      $new_name = $this->sanitizeFile($new_name);
      $this->forward404Unless(is_file($absCurrentDir.'/'.$name));
    }

    @rename($absCurrentDir.'/'.$name, $absCurrentDir.'/'.$new_name);
    
    if($this->use_thumbnails && ($type === 'file') && file_exists($absCurrentDir.'/'.$this->thumbnails_dir.'/'.$name))
    {
      @rename($absCurrentDir.'/'.$this->thumbnails_dir.'/'.$name, $absCurrentDir.'/'.$this->thumbnails_dir.'/'.$new_name);
    }

    $this->absCurrentDir = $absCurrentDir;
    $this->info = array();
    if (is_dir($absCurrentDir.'/'.$new_name) and ($type === 'folder'))
    {
      $this->name = $new_name;
    }
    else if (is_file($absCurrentDir.'/'.$new_name) and ($type === 'file'))
    {
      $this->name = $new_name;
      $this->info = $this->getInfo($new_name);
    }
    else
    {
      $this->name = $name;
      $this->info = $this->getInfo($name);
    }
    
    $this->type = $type;
  }

  protected function getInfo($filename)
  {
    $info = array();
    $info['ext']  = substr($filename, strpos($filename, '.') - strlen($filename) + 1);
    $stats = stat($this->absCurrentDir.'/'.$filename);
    $info['size'] = $stats['size'];
    if($this->isImage($info['ext']))
    {
      if($this->use_thumbnails && is_readable(sfConfig::get('sf_web_dir').$this->webAbsCurrentDir.'/'.$this->thumbnails_dir.'/'.$filename))
      {
        $info['icon'] = $this->webAbsCurrentDir.'/'.$this->thumbnails_dir.'/'.$filename;
        $info['size'] = 0;
      }
      else
      {
        $info['icon'] = $this->webAbsCurrentDir.'/'.$filename;
      }
    }
    else
    {
      if(is_readable(sfConfig::get('sf_web_dir').'/sfMediaLibraryPlugin/images/'.$info['ext'].'.png'))
      {
        $info['icon'] = '/sfMediaLibraryPlugin/images/'.$info['ext'].'.png';
      }
      else
      {
        $info['icon'] = '/sfMediaLibraryPlugin/images/unknown.png';
      }
    }
    
    return $info;
  }

  public function executeUpload()
  {
    $currentDir = $this->dot2slash($this->getRequestParameter('current_dir'));
    $webAbsCurrentDir = '/'.sfConfig::get('sf_upload_dir_name').'/assets/'.$currentDir;
    $absCurrentDir = sfConfig::get('sf_upload_dir').'/assets/'.$currentDir;

    $this->forward404Unless(is_dir($absCurrentDir));

    $fileName = $this->sanitizeFile($this->getRequest()->getFileName('file'));

    if($this->use_thumbnails)
    {
      if(!is_dir($absCurrentDir.'/'.$this->thumbnails_dir))
      {
        // If the thumbnails directory doesn't exist, create it now
        $old = umask(0000);
        @mkdir($absCurrentDir.'/'.$this->thumbnails_dir, 0777);
        umask($old);
      }
      $thumbnail = new sfThumbnail(64, 64);
      $thumbnail->loadFile($this->getRequest()->getFilePath('file'));
      $thumbnail->save($absCurrentDir.'/'.$this->thumbnails_dir.'/'.$fileName);
    }
    $this->getRequest()->moveFile('file', $absCurrentDir.'/'.$fileName);

    $this->redirect('sfMediaLibrary/index?dir='.$this->getRequestParameter('current_dir'));
  }

  public function executeDelete()
  {
    $currentDir = $this->dot2slash($this->getRequestParameter('current_path'));
    $currentFile = $this->getRequestParameter('name');
    $absCurrentFile = sfConfig::get('sf_upload_dir').'/assets/'.$currentDir.'/'.$currentFile;

    $this->forward404Unless(is_readable($absCurrentFile));

    unlink($absCurrentFile);
    
    if($this->use_thumbnails)
    {
      $absThumbnailFile = sfConfig::get('sf_upload_dir').'/assets/'.$currentDir.'/'.$this->thumbnails_dir.'/'.$currentFile;
      if(is_readable($absThumbnailFile))
      {
        unlink($absThumbnailFile);
      }
    }

    $this->redirect('sfMediaLibrary/index?dir='.$this->getRequestParameter('current_path'));
  }

  public function executeMkdir()
  {
    $currentDir = $this->dot2slash($this->getRequestParameter('current_dir'));
    $dirName = $this->sanitizeDir($this->getRequestParameter('name'));
    $absCurrentDir = sfConfig::get('sf_upload_dir').'/assets/'.(empty($currentDir) ? '' : $currentDir.'/').$dirName;

    $old = umask(0000);
    @mkdir($absCurrentDir, 0777);
    if($this->use_thumbnails)
    {
      @mkdir($absCurrentDir.'/'.$this->thumbnails_dir, 0777);
    }
    umask($old);

    $this->redirect('sfMediaLibrary/index?dir='.$this->getRequestParameter('current_dir'));
  }

  public function executeRmdir()
  {
    $currentDir = $this->dot2slash('.'.$this->getRequestParameter('current_path'));
    $absCurrentDir = sfConfig::get('sf_upload_dir').'/assets/'.$currentDir.'/'.$this->getRequestParameter('name');

    $this->forward404Unless(is_dir($absCurrentDir));

    if($this->use_thumbnails && is_readable($absCurrentDir.'/'.$this->thumbnails_dir))
    {
      rmdir($absCurrentDir.'/'.$this->thumbnails_dir);
    }
    
    rmdir($absCurrentDir);

    $this->redirect('sfMediaLibrary/index?dir='.$this->getRequestParameter('current_path'));
  }
  
  protected function dot2slash($txt)
  {
    return preg_replace('#[\+\s]+#', '/', $txt);
  }

  protected function slash2dot($txt)
  {
    return preg_replace('#/+#', '+', $txt);
  }

  protected function sanitizeDir($dir)
  {
    return preg_replace('/[^a-z0-9_-]/i', '_', $dir);
  }

  protected function sanitizeFile($file)
  {
    return preg_replace('/[^a-z0-9_\.-]/i', '_', $file);
  }
}
