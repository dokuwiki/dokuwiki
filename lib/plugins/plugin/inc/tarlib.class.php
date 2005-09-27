<?php

/*
 +---------------------------------------------+
 |   TAR format class - Creates TAR archives   |
 +---------------------------------------------+
 |   This class is part or the MaxgComp suite  |
 +---------------------------------------------+
 |   Created by the Maxg Network (maxg.info)   |
 |  http://docs.maxg.info for help & license.  |
 +---------------------------------------------+
 | Author: Bouchon <tarlib@bouchon.org> (Maxg) |
 +---------------------------------------------+
*  Modified for Dokuwiki
*  @author    Christopher Smith <chris@jalakai.co.uk>
*/

define('COMPRESS_GZIP',1);
define('COMPRESS_BZIP',2);
define('COMPRESS_AUTO',3);
define('COMPRESS_NONE',0);

define('TARLIB_VERSION','1.2');
define('FULL_ARCHIVE',-1);

define('ARCHIVE_DYNAMIC',0);
define('ARCHIVE_RENAMECOMP',5);
define('COMPRESS_DETECT',-1);

class CompTar
{
  var $_comptype;
  var $_compzlevel;
  var $_fp;
  var $_memdat;
  var $_nomf;
  var $_result;

  function CompTar($p_filen = ARCHIVE_DYNAMIC , $p_comptype = COMPRESS_AUTO, $p_complevel = 9)
  {
    $this->_nomf = $p_filen; $flag=0;
    if($p_comptype && $p_comptype % 5 == 0){$p_comptype /= ARCHIVE_RENAMECOMP; $flag=1;}

    if($p_complevel > 0 && $p_complevel <= 9) $this->_compzlevel = $p_complevel;
    else $p_complevel = 9;

    if($p_comptype == COMPRESS_DETECT)
    {
      if(strtolower(substr($p_filen,-3)) == '.gz') $p_comptype = COMPRESS_GZIP;
      elseif(strtolower(substr($p_filen,-4)) == '.bz2') $p_comptype = COMPRESS_BZIP;
      else $p_comptype = COMPRESS_NONE;
    }

    switch($p_comptype)
    {
      case COMPRESS_GZIP:
        if(!extension_loaded('zlib')) $this->_result = -1;
        $this->_comptype = COMPRESS_GZIP;
      break;

      case COMPRESS_BZIP:
        if(!extension_loaded('bz2')) $this->_result = -2;
        $this->_comptype = COMPRESS_BZIP;
      break;

      case COMPRESS_AUTO:
        if(extension_loaded('zlib'))
          $this->_comptype = COMPRESS_GZIP;
        elseif(extension_loaded('bz2'))
          $this->_comptype = COMPRESS_BZIP;
        else
          $this->_comptype = COMPRESS_NONE;
      break;

      default:
        $this->_comptype = COMPRESS_NONE;
    }

    if($this->_result < 0) $this->_comptype = COMPRESS_NONE;

    if($flag) $this->_nomf.= '.'.$this->getCompression(1);
    $this->_result = true;
  }

  function setArchive($p_name='', $p_comp = COMPRESS_AUTO, $p_level=9)
  {
    $this->_CompTar();
    $this->CompTar($p_name, $p_comp, $p_level);
    return $this->_result;
  }

  function getCompression($ext = false)
  {
    $exts = Array('tar','tar.gz','tar.bz2');
    if($ext) return $exts[$this->_comptype];
    return $this->_comptype;
  }

  function setCompression($p_comp = COMPRESS_AUTO)
  {
    $this->setArchive($this->_nomf, $p_comp, $this->_compzlevel);
    return $this->_compzlevel;
  }

  function getDynamicArchive()
  {
    return $this->_encode($this->_memdat);
  }

  function writeArchive($p_archive)
  {
    if(!$this->_memdat) return -7;
    $fp = @fopen($p_archive, 'wb');
    if(!$fp) return -6;

    fwrite($fp, $this->_memdat);
    fclose($fp);

    return true;
  }

  function sendClient($name = '', $archive = '', $headers = TRUE)
  {
    if(!$name && !$this->_nomf) return -9;
    if(!$archive && !$this->_memdat) return -10;
    if(!$name) $name = basename($this->_nomf);

    if($archive){ if(!file_exists($archive)) return -11; }
    else $decoded = $this->getDynamicArchive();

    if($headers)
    {
      header('Content-Type: application/x-gtar');
      header('Content-Disposition: attachment; filename='.basename($name));
      header('Accept-Ranges: bytes');
      header('Content-Length: '.($archive ? filesize($archive) : strlen($decoded)));
    }

    if($archive)
    {
      $fp = @fopen($archive,'rb');
      if(!$fp) return -4;

      while(!foef($fp)) echo fread($fp,2048);
    }
    else
    {
      echo $decoded;
    }

    return true;
  }

  function Extract($p_what = FULL_ARCHIVE, $p_to = '.', $p_remdir='', $p_mode = 0755)
  {
    if(!$this->_OpenRead()) return -4;
//  if(!@is_dir($p_to)) if(!@mkdir($p_to, 0777)) return -8;   --CS
    if(!@is_dir($p_to)) if(!$this->_dirApp($p_to)) return -8;   //--CS (route through correct dir fn)

    $ok = $this->_extractList($p_to, $p_what, $p_remdir, $p_mode);
    $this->_CompTar();

    return $ok;
  }

  function Create($p_filelist,$p_add='',$p_rem='')
  {
    if(!$fl = $this->_fetchFilelist($p_filelist)) return -5;
    if(!$this->_OpenWrite()) return -6;

    $ok = $this->_addFileList($fl,$p_add,$p_rem);

    if($ok) $this->_writeFooter();
    else{ $this->_CompTar(); @unlink($this->_nomf); }

    return $ok;
  }

  function Add($p_filelist, $p_add = '', $p_rem = '')
  {
    if (($this->_nomf != ARCHIVE_DYNAMIC && @is_file($this->_nomf)) || ($this->_nomf == ARCHIVE_DYNAMIC && !$this->_memdat))
      return $this->Create($p_filelist, $p_add, $p_rem);

    if(!$fl = $this->_fetchFilelist($p_filelist)) return -5;
    return $this->_append($fl, $p_add, $p_rem);
  }

  function ListContents()
  {
    if(!$this->_nomf) return -3;
    if(!$this->_OpenRead()) return -4;

    $result = Array();

    while ($dat = $this->_read(512))
    {
      $dat = $this->_readHeader($dat);
      if(!is_array($dat)) continue;

      $this->_seek(ceil($dat['size']/512)*512,1);
      $result[] = $dat;
    }

    return  $result;
  }

  function TarErrorStr($i)
  {
    $ecodes = Array(
         1 => TRUE,
         0 => "Undocumented error",
        -1 => "Can't use COMPRESS_GZIP compression : ZLIB extensions are not loaded !",
        -2 => "Can't use COMPRESS_BZIP compression : BZ2 extensions are not loaded !",
        -3 => "You must set a archive file to read the contents !",
        -4 => "Can't open the archive file for read !",
        -5 => "Invalide file list !",
        -6 => "Can't open the archive in write mode !",
        -7 => "There is no ARCHIVE_DYNAMIC to write !",
        -8 => "Can't create the directory to extract files !",
        -9 => "Please pass a archive name to send if you made created an ARCHIVE_DYNAMIC !",
       -10 => "You didn't pass an archive filename and there is no stored ARCHIVE_DYNAMIC !",
       -11 => "Given archive doesn't exist !"
    );

    return isset($ecodes[$i]) ? $ecodes[$i] : $ecodes[0];
  }

  function TarInfo($headers = true)
  {
    if($headers)
    {
    ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>

<head>
  <title>MaxgComp TAR</title>
  <style type="text/css">
   body{margin: 20px;}
   body,td{font-size:10pt;font-family: arial;}
  </style>
  <meta name="Author" content="The Maxg Network, http://maxg.info" />
  <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
</head>

<body bgcolor="#EFEFEF">
<?php
    }
?>
<table border="0" align="center" width="500" cellspacing="4" cellpadding="5" style="border:1px dotted black;">
<tr>
  <td align="center" bgcolor="#DFDFEF" colspan="3" style="font-size:15pt;font-color:#330000;border:1px solid black;">MaxgComp TAR</td>
</tr>
<tr>
  <td colspan="2" bgcolor="#EFEFFE" style="border:1px solid black;">This software was created by the Maxg Network, <a href="http://maxg.info" target="_blank" style="text-decoration:none;color:#333366;">http://maxg.info</a>
   <br />It is distributed under the GNU <a href="http://www.gnu.org/copyleft/lesser.html" target="_blank" style="text-decoration:none;color:#333366;">Lesser General Public License</a>
   <br />You can find the documentation of this class <a href="http://docs.maxg.info" target="_blank" style="text-decoration:none;color:#333366;">here</a></td>
   <td width="60" bgcolor="#EFEFFE" style="border:1px solid black;" align="center"><img src="http://img.maxg.info/menu/tar.gif" border="0" alt="MaxgComp TAR" /></td>
</tr>
<tr>
  <td width="50%" align="center" style="border:1px solid black;" bgcolor="#DFDFEF">MaxgComp TAR version</td>
  <td colspan="2" align="center" bgcolor="#EFEFFE" style="border:1px solid black;"><?=TARLIB_VERSION?></td>
</tr>
<tr>
  <td width="50%" align="center" style="border:1px solid black;" bgcolor="#DFDFEF">ZLIB extensions</td>
  <td colspan="2" align="center" bgcolor="#EFEFFE" style="border:1px solid black;"><?=(extension_loaded('zlib') ? '<b>Yes</b>' : '<i>No</i>')?></td>
</tr>
<tr>
  <td width="50%" align="center" style="border:1px solid black;" bgcolor="#DFDFEF">BZ2 extensions</td>
  <td colspan="2" align="center" bgcolor="#EFEFFE" style="border:1px solid black;"><?=(extension_loaded('bz2') ? '<b>Yes</b>' : '<i>No</i>')?></td>
</tr>
<tr>
  <td width="50%" align="center" style="border:1px solid black;" bgcolor="#DFDFEF">Allow URL fopen</td>
  <td colspan="2" align="center" bgcolor="#EFEFFE" style="border:1px solid black;"><?=(ini_get('allow_url_fopen') ? '<b>Yes</b>' : '<i>No</i>')?></td>
</tr>
<tr>
  <td width="50%" align="center" style="border:1px solid black;" bgcolor="#DFDFEF">Time limit</td>
  <td colspan="2" align="center" bgcolor="#EFEFFE" style="border:1px solid black;"><?=ini_get('max_execution_time')?></td>
</tr>
<tr>
  <td width="50%" align="center" style="border:1px solid black;" bgcolor="#DFDFEF">PHP Version</td>
  <td colspan="2" align="center" bgcolor="#EFEFFE" style="border:1px solid black;"><?=phpversion()?></td>
</tr>
<tr>
  <td colspan="3" align="center" bgcolor="#EFEFFE" style="border:1px solid black;">
    <i>Special thanks to &laquo; Vincent Blavet &raquo; for his PEAR::Archive_Tar class</i>
  </td>
</tr>
</table>
<?php
    if($headers) echo '</body></html>';
  }

  function _seek($p_flen, $tell=0)
  {
    if($this->_nomf === ARCHIVE_DYNAMIC)
      $this->_memdat=substr($this->_memdat,0,($tell ? strlen($this->_memdat) : 0) + $p_flen);
    elseif($this->_comptype == COMPRESS_GZIP)
      @gzseek($this->_fp, ($tell ? @gztell($this->_fp) : 0)+$p_flen);
    elseif($this->_comptype == COMPRESS_BZIP)
      @fseek($this->_fp, ($tell ? @ftell($this->_fp) : 0)+$p_flen);
    else
      @fseek($this->_fp, ($tell ? @ftell($this->_fp) : 0)+$p_flen);
  }

  function _OpenRead()
  {
    if($this->_comptype == COMPRESS_GZIP)
      $this->_fp = @gzopen($this->_nomf, 'rb');
    elseif($this->_comptype == COMPRESS_BZIP)
      $this->_fp = @bzopen($this->_nomf, 'rb');
    else
      $this->_fp = @fopen($this->_nomf, 'rb');

    return ($this->_fp ? true : false);
  }

  function _OpenWrite($add = 'w')
  {
    if($this->_nomf === ARCHIVE_DYNAMIC) return true;

    if($this->_comptype == COMPRESS_GZIP)
      $this->_fp = @gzopen($this->_nomf, $add.'b'.$this->_compzlevel);
    elseif($this->_comptype == COMPRESS_BZIP)
      $this->_fp = @bzopen($this->_nomf, $add.'b');
    else
      $this->_fp = @fopen($this->_nomf, $add.'b');

    return ($this->_fp ? true : false);
  }

  function _CompTar()
  {
    if($this->_nomf === ARCHIVE_DYNAMIC || !$this->_fp) return;

    if($this->_comptype == COMPRESS_GZIP) @gzclose($this->_fp);
    elseif($this->_comptype == COMPRESS_BZIP) @bzclose($this->_fp);
    else @fclose($this->_fp);
  }

  function _read($p_len)
  {
    if($this->_comptype == COMPRESS_GZIP)
      return @gzread($this->_fp,$p_len);
    elseif($this->_comptype == COMPRESS_BZIP)
      return @bzread($this->_fp,$p_len);
    else
      return @fread($this->_fp,$p_len);
  }

  function _write($p_data)
  {
    if($this->_nomf === ARCHIVE_DYNAMIC) $this->_memdat .= $p_data;
    elseif($this->_comptype == COMPRESS_GZIP)
      return @gzwrite($this->_fp,$p_data);

    elseif($this->_comptype == COMPRESS_BZIP)
      return @bzwrite($this->_fp,$p_data);

    else
      return @fwrite($this->_fp,$p_data);
  }

  function _encode($p_dat)
  {
    if($this->_comptype == COMPRESS_GZIP)
      return gzencode($p_dat, $this->_compzlevel);
    elseif($this->_comptype == COMPRESS_BZIP)
      return bzcompress($p_dat, $this->_compzlevel);
    else return $p_dat;
  }

  function _readHeader($p_dat)
  {
    if (!$p_dat || strlen($p_dat) != 512) return false;

    for ($i=0, $chks=0; $i<148; $i++)
      $chks += ord($p_dat[$i]);

    for ($i=156,$chks+=256; $i<512; $i++)
      $chks += ord($p_dat[$i]);

    $headers = @unpack("a100filename/a8mode/a8uid/a8gid/a12size/a12mtime/a8checksum/a1typeflag/a100link/a6magic/a2version/a32uname/a32gname/a8devmajor/a8devminor", $p_dat);
    if(!$headers) return false;

    $return['checksum'] = OctDec(trim($headers['checksum']));
    if ($return['checksum'] != $chks) return false;

    $return['filename'] = trim($headers['filename']);
    $return['mode'] = OctDec(trim($headers['mode']));
    $return['uid'] = OctDec(trim($headers['uid']));
    $return['gid'] = OctDec(trim($headers['gid']));
    $return['size'] = OctDec(trim($headers['size']));
    $return['mtime'] = OctDec(trim($headers['mtime']));
    $return['typeflag'] = $headers['typeflag'];
    $return['link'] = trim($headers['link']);
    $return['uname'] = trim($headers['uname']);
    $return['gname'] = trim($headers['gname']);

    return $return;
  }

  function _fetchFilelist($p_filelist)
  {
    if(!$p_filelist || (is_array($p_filelist) && !@count($p_filelist))) return false;

    if(is_string($p_filelist))
    {
        $p_filelist = explode('|',$p_filelist);
        if(!is_array($p_filelist)) $p_filelist = Array($p_filelist);
    }

    return $p_filelist;
  }

  function _addFileList($p_fl, $p_addir, $p_remdir)
  {
    foreach($p_fl as $file)
    {
      if(($file == $this->_nomf && $this->_nomf != ARCHIVE_DYNAMIC) || !$file || (!file_exists($file) && !is_array($file)))
        continue;

      if (!$this->_addFile($file, $p_addir, $p_remdir))
        continue;

      if (@is_dir($file))
      {
        $d = @opendir($file);

        if(!$d) continue;
        readdir($d); readdir($d);

        while($f = readdir($d))
        {
          if($file != ".") $tmplist[0] = "$file/$f";
          else $tmplist[0] = $d;

          $this->_addFileList($tmplist, $p_addir, $p_remdir);
        }

        closedir($d); unset($tmplist,$f);
      }
    }
    return true;
  }

  function _addFile($p_fn, $p_addir = '', $p_remdir = '')
  {
    if(is_array($p_fn)) list($p_fn, $data) = $p_fn;
    $sname = $p_fn;

    if($p_remdir)
    {
        if(substr($p_remdir,-1) != '/') $p_remdir .= "/";

        if(substr($sname, 0, strlen($p_remdir)) == $p_remdir)
          $sname = substr($sname, strlen($p_remdir));
    }

    if($p_addir) $sname = $p_addir.(substr($p_addir,-1) == '/' ? '' : "/").$sname;

    if(strlen($sname) > 99) return;

    if(@is_dir($p_fn))
    {
      if(!$this->_writeFileHeader($p_fn, $sname)) return false;
    }
    else
    {
     if(!$data)
     {
      $fp = fopen($p_fn, 'rb');
      if(!$fp) return false;
     }

     if(!$this->_writeFileHeader($p_fn, $sname, ($data ? strlen($data) : FALSE))) return false;

     if(!$data)
     {
      while(!feof($fp))
      {
        $packed = pack("a512", fread($fp,512));
        $this->_write($packed);
      }
      fclose($fp);
     }
     else
     {
      for($s = 0; $s < strlen($data); $s += 512)
        $this->_write(pack("a512",substr($data,$s,512)));
     }
    }

    return true;
  }

  function _writeFileHeader($p_file, $p_sname, $p_data=false)
  {
   if(!$p_data)
   {
    if (!$p_sname) $p_sname = $p_file;
    $p_sname = $this->_pathTrans($p_sname);

    $h_info = stat($p_file);
    $h[0] = sprintf("%6s ", DecOct($h_info[4]));
    $h[] = sprintf("%6s ", DecOct($h_info[5]));
    $h[] = sprintf("%6s ", DecOct(fileperms($p_file)));
    clearstatcache();
    $h[] = sprintf("%11s ", DecOct(filesize($p_file)));
    $h[] = sprintf("%11s", DecOct(filemtime($p_file)));

    $dir = @is_dir($p_file) ? '5' : '';
   }
   else
   {
    $dir = '';
    $p_data = sprintf("%11s ", DecOct($p_data));
    $time = sprintf("%11s ", DecOct(time()));
    $h = Array("     0 ","     0 "," 40777 ",$p_data,$time);
   }

    $data_first = pack("a100a8a8a8a12A12", $p_sname, $h[2], $h[0], $h[1], $h[3], $h[4]);
    $data_last = pack("a1a100a6a2a32a32a8a8a155a12", $dir, '', '', '', '', '', '', '', '', "");

     for ($i=0,$chks=0; $i<148; $i++)
       $chks += ord($data_first[$i]);

     for ($i=156, $chks+=256, $j=0; $i<512; $i++, $j++)
       $chks += ord($data_last[$j]);

     $this->_write($data_first);

     $chks = pack("a8",sprintf("%6s ", DecOct($chks)));
     $this->_write($chks.$data_last);

     return true;
  }

  function _append($p_filelist, $p_addir="", $p_remdir="")
  {
    if(!$this->_fp) if(!$this->_OpenWrite('a')) return -6;

    if($this->_nomf == ARCHIVE_DYNAMIC)
    {
      $s = strlen($this->_memdat);
      $this->_memdat = substr($this->_memdat,0,-512);
    }
    else
    {
      $s = filesize($this->_nomf);
      $this->_seek($s-512);
    }

    $ok = $this->_addFileList($p_filelist, $p_addir, $p_remdir);
    $this->_writeFooter();

    return $ok;
  }

  function _pathTrans($p_dir)
  {
    if ($p_dir)
    {
      $subf = explode("/", $p_dir); $r='';

      for ($i=count($subf)-1; $i>=0; $i--)
      {
        if ($subf[$i] == ".") {}
        else if ($subf[$i] == "..") $i--;
        else if (!$subf[$i] && $i!=count($subf)-1 && $i) {}
        else $r = $subf[$i].($i!=(count($subf)-1) ? "/".$r : "");
      }
    }
    return $r;
  }

  function _writeFooter()
  {
    $this->_write(pack("a512", ""));
  }

  function _extractList($p_to, $p_files, $p_remdir, $p_mode = 0755)
  {
    if (!$p_to || ($p_to[0]!="/"&&substr($p_to,0,3)!="../"&&substr($p_to,1,3)!=":\\")) /*" // <- PHP Coder bug */
      $p_to = "./$p_to";

    if ($p_remdir && substr($p_remdir,-1)!='/') $p_remdir .= '/';
    $p_remdirs = strlen($p_remdir);
    while($dat = $this->_read(512))
    {
      $headers = $this->_readHeader($dat);
      if(!$headers['filename']) continue;

      if($p_files == -1 || $p_files[0] == -1) $extract = true;
      else
      {
        $extract = false;

        foreach($p_files as $f)
        {
          if(substr($f,-1) == "/") {
            if((strlen($headers['filename']) > strlen($f)) && (substr($headers['filename'],0,strlen($f))==$f)) {
              $extract = true; break;
            }
          }
          elseif($f == $headers['filename']) {
            $extract = true; break;
          }
        }
      }

      if ($extract)
      {
        $det[] = $headers;
        if ($p_remdir && substr($headers['filename'],0,$p_remdirs)==$p_remdir)
          $headers['filename'] = substr($headers['filename'],$p_remdirs);

        if($headers['filename'].'/' == $p_remdir && $headers['typeflag']=='5') continue;

        if ($p_to != "./" && $p_to != "/")
        {
          while($p_to{-1}=="/") $p_to = substr($p_to,0,-1);

          if($headers['filename']{0} == "/")
            $headers['filename'] = $p_to.$headers['filename'];
          else
            $headers['filename'] = $p_to."/".$headers['filename'];
        }

        $ok = $this->_dirApp($headers['typeflag']=="5" ? $headers['filename'] : dirname($headers['filename']));
        if($ok < 0) return $ok;

        if (!$headers['typeflag'])
        {
          if (!$fp = @fopen($headers['filename'], "wb")) return -6;
          $n = floor($headers['size']/512);

          for ($i=0; $i<$n; $i++) fwrite($fp, $this->_read(512),512);
          if (($headers['size'] % 512) != 0) fwrite($fp, $this->_read(512), $headers['size'] % 512);

          fclose($fp);
          touch($headers['filename'], $headers['mtime']);
          chmod($headers['filename'], $p_mode);
        }
       else
       {
         $this->_seek(ceil($headers['size']/512)*512,1);
       }
      }else $this->_seek(ceil($headers['size']/512)*512,1);
    }
    return $det;
  }

function _dirApp($d)
  {
//  map to dokuwiki function (its more robust)
    return ap_mkdir($d);  
/*
    $d = explode('/', $d);
    $base = '';

    foreach($d as $f)
    {
      if(!is_dir($base.$f))
      {
        $ok = @mkdir($base.$f, 0777);
        if(!$ok) return false;
      }
      $base .= "$f/";
    }
*/    
  }

}

