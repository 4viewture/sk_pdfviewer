<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2006 Steffen Kamper <steffen@sk-typo3.de>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * Plugin 'PDF Viewer' for the 'sk_pdfviewer' extension.
 *
 * @author	Steffen Kamper <steffen@sk-typo3.de>
 */


require_once(PATH_tslib.'class.tslib_pibase.php');
require_once (PATH_t3lib."class.t3lib_stdgraphic.php");


class tx_skpdfviewer_pi1 extends tslib_pibase {
	var $prefixId = 'tx_skpdfviewer_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_skpdfviewer_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey = 'sk_pdfviewer';	// The extension key.
	var $pi_checkCHash = TRUE;
	var $CE_ID;       
    
	/**
	 * The main method of the PlugIn
	 *
	 * @param	string		$content: The PlugIn content
	 * @param	array		$conf: The PlugIn configuration
	 * @return	The content that is displayed on the website
	 */
	function main($content,$conf)	{
		$this->conf=$conf;
		$this->pi_setPiVarDefaults();
		$this->pi_loadLL();
		$this->pi_initPIflexForm();  
        
        $CE_ID=substr($this->cObj->currentRecord,11);     
        
        //process flexform
	    if($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'pdf_file', 'sPARAMS')!='') $this->conf['pdf_file'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'pdf_file', 'sPARAMS');   
        if($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'format', 'sPARAMS')!='') $this->conf['file_format'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'format', 'sPARAMS');   
        if($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'title', 'sPARAMS')!='') $this->conf['title'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'title', 'sPARAMS');   
        if($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'width', 'sPARAMS')!='') $this->conf['width'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'width', 'sPARAMS');   
        if($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'height', 'sPARAMS')!='') $this->conf['height'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'height', 'sPARAMS');   
        if($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'pages', 'sPARAMS')!='') $this->conf['pages'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'pages', 'sPARAMS');   
        if($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'startpage', 'sPARAMS')!='') $this->conf['startpage'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'startpage', 'sPARAMS');   
        if($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'endpage', 'sPARAMS')!='') $this->conf['endpage'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'endpage', 'sPARAMS');   
        if($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'titletag', 'sPARAMS')!='') $this->conf['titletag'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'titletag', 'sPARAMS');   
        if($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'alttag', 'sPARAMS')!='') $this->conf['alttag'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'alttag', 'sPARAMS');   
        if($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'addparams', 'sPARAMS')!='') $this->conf['addparams'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'addparams', 'sPARAMS');   
        if($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'IMparams', 'sPARAMS')!='') $this->conf['IMparams'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'IMparams', 'sPARAMS');   
        if($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'link', 'sPARAMS')!='') $this->conf['link.']['parameter'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'link', 'sPARAMS');   
        if($this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'link2doc', 'sPARAMS')!='') $this->conf['link2doc'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'], 'link2doc', 'sPARAMS');   
        
        if(intval($this->conf['pages'])==0) $this->conf['pages']=1;    
        if(intval($this->conf['endpage'])==0) $this->conf['endpage']=$this->conf['pages'];
        
        $template=$this->cObj->getSubpart($this->cObj->fileResource($this->conf['templateFile']),'###PDFVIEWER###');  
        
        $pdfID=intval($this->piVars['pdfid']);
        
        $page=intval($this->piVars['page']);
        $nextpage=intval($this->piVars['topage'])-1;
        if(isset($this->piVars['prevpage'])) $page==$nextpage?$page -=1:$page=$nextpage;
        if(isset($this->piVars['nextpage'])) $page==$nextpage?$page +=1:$page=$nextpage;
        
        if($page<intval($this->conf['startpage'])) $page=intval($this->conf['startpage']);
        if($page+1>intval($this->conf['endpage'])) $page=intval($this->conf['endpage'])-1;
        
        
        if($this->conf['file_format']=='') $this->conf['file_format']='gif';
        
	    $imageProc = t3lib_div::makeInstance('t3lib_stdGraphic');
        $imageProc->init();
        $imageProc->tempPath = PATH_site.'typo3temp/';

        #imageMagickConvert($imagefile,$newExt='',$w='',$h='',$params='',$frame='',$options='',$mustCreate=0)   
        $ret=$imageProc->imageMagickConvert('uploads/tx_skpdfviewer/'.$this->conf['pdf_file'],$this->conf['file_format'],$this->conf['width'],$this->conf['height'],$this->conf['IMparams'],$page,"",1);
        
        $picurl=substr($ret[3],strpos($ret[3],'typo3temp'));
        //replace Markers
        $markerArray['###HIDDEN###']='<input type="hidden" name="'.$this->prefixId.'[page]" value="'.$page.'" /><input type="hidden" name="'.$this->prefixId.'[pdfid]" value="" />';
        $markerArray['###TITLE###']=$this->conf['title'];
        $markerArray['###L_PAGE###']=$this->pi_getLL('page');
        $markerArray['###PAGE###']=$page+1;
        $markerArray['###L_PAGES###']=$this->pi_getLL('of');
        $markerArray['###PAGES###']=$this->conf['pages'];
        $markerArray['###ID###']=$this->cObj->data['uid'];
        $markerArray['###ACTION###']=$this->cObj->typolink_url(array('parameter'=>$GLOBALS['TSFE']->id,'section'=>'pdf-'.$this->cObj->data['uid']));
        $markerArray['###JUMPTOPAGE_NAME###']=$this->prefixId.'[topage]';
        $markerArray['###JUMPTOTEXT_VALUE###']=$page+1;
        $markerArray['###PIC###']=$picurl;
        $markerArray['###WIDTH###']=$ret[0];
        $markerArray['###HEIGHT###']=$ret[1];
        $markerArray['###TITLETAG###']=$this->conf['titletag'];
        $markerArray['###ALTTAG###']=$this->conf['alttag'];
        $markerArray['###ADDITIONALPARAMS###']=$this->conf['addparams'];
        
        $markerArray['###PREVIOUSPAGE_NAME###']=$this->prefixId.'[prevpage]';
        $markerArray['###PREVIOUSPAGE_VALUE###']='&lt;';
        $markerArray['###NEXTPAGE_NAME###']=$this->prefixId.'[nextpage]';
        $markerArray['###NEXTPAGE_VALUE###']='&gt;';
        
        if($this->conf['link.']['parameter']!='' && !$this->conf['link2doc']) {
            $subpartLinkWrapArray['###LINK###'] = explode('|', $this->cObj->typolink('|',$this->conf['link.']));
        }
        if($this->conf['link2doc']) {
            $subpartLinkWrapArray['###LINK###'] = explode('|', $this->cObj->typolink('|',array('parameter'=>'uploads/tx_skpdfviewer/'.$this->conf['pdf_file'])));
        } 
                
        $content=$this->cObj->substituteMarkerArrayCached($template,$markerArray,$subpartArray,$subpartLinkWrapArray);
	
		return $this->pi_wrapInBaseClass('<div class="pdf" style="width:'.$ret[0].'px;">'.$content.'</div>');
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/sk_pdfviewer/pi1/class.tx_skpdfviewer_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/sk_pdfviewer/pi1/class.tx_skpdfviewer_pi1.php']);
}

?>
