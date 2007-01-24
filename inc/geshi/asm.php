<?php
/*************************************************************************************
 * asm.php
 * -------
 * Author: Tux (tux@inmail.cz)
 * Copyright: (c) 2004 Tux (http://tux.a4.cz/), Nigel McNie (http://qbnz.com/highlighter)
 * Release Version: 1.0.7.17
 * CVS Revision Version: $Revision: 866 $
 * Date Started: 2004/07/27
 * Last Modified: $Date: 2006-11-26 21:40:26 +1300 (Sun, 26 Nov 2006) $
 *
 * x86 Assembler language file for GeSHi.
 * Words are from SciTe configuration file (based on NASM syntax)
 *
 * CHANGES
 * -------
 * 2004/11/27 (1.0.2)
 *  -  Added support for multiple object splitters
 * 2004/10/27 (1.0.1)
 *   -  Added support for URLs
 *   -  Added binary and hexadecimal regexps
 * 2004/08/05 (1.0.0)
 *   -  First Release
 *
 * TODO (updated 2004/11/27)
 * -------------------------
 *
 *************************************************************************************
 *
 *     This file is part of GeSHi.
 *
 *   GeSHi is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 *   GeSHi is distributed in the hope that it will be useful,
 *   but WITHOUT ANY WARRANTY; without even the implied warranty of
 *   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *   GNU General Public License for more details.
 *
 *   You should have received a copy of the GNU General Public License
 *   along with GeSHi; if not, write to the Free Software
 *   Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 ************************************************************************************/

$language_data = array (
	'LANG_NAME' => 'ASM',
	'COMMENT_SINGLE' => array(1 => ';'),
	'COMMENT_MULTI' => array(),
	'CASE_KEYWORDS' => GESHI_CAPS_NO_CHANGE,
	'QUOTEMARKS' => array("'", '"'),
	'ESCAPE_CHAR' => '',
	'KEYWORDS' => array(
		/*CPU*/
		1 => array(
		       'aaa','aad','aam','aas','adc','add','and','call','cbw','clc','cld','cli','cmc','cmp',
			'cmps','cmpsb','cmpsw','cwd','daa','das','dec','div','esc','hlt','idiv','imul','in','inc',
			'int','into','iret','ja','jae','jb','jbe','jc','jcxz','je','jg','jge','jl','jle','jmp',
			'jna','jnae','jnb','jnbe','jnc','jne','jng','jnge','jnl','jnle','jno','jnp','jns','jnz',
			'jo','jp','jpe','jpo','js','jz','lahf','lds','lea','les','lods','lodsb','lodsw','loop',
			'loope','loopew','loopne','loopnew','loopnz','loopnzw','loopw','loopz','loopzw','mov',
			'movs','movsb','movsw','mul','neg','nop','not','or','out','pop','popf','push','pushf',
			'rcl','rcr','ret','retf','retn','rol','ror','sahf','sal','sar','sbb','scas','scasb','scasw',
			'shl','shr','stc','std','sti','stos','stosb','stosw','sub','test','wait','xchg','xlat',
			'xlatb','xor','bound','enter','ins','insb','insw','leave','outs','outsb','outsw','popa','pusha','pushw',
			'arpl','lar','lsl','sgdt','sidt','sldt','smsw','str','verr','verw','clts','lgdt','lidt','lldt','lmsw','ltr',
			'bsf','bsr','bt','btc','btr','bts','cdq','cmpsd','cwde','insd','iretd','iretdf','iretf',
			'jecxz','lfs','lgs','lodsd','loopd','looped','loopned','loopnzd','loopzd','lss','movsd',
			'movsx','movzx','outsd','popad','popfd','pushad','pushd','pushfd','scasd','seta','setae',
			'setb','setbe','setc','sete','setg','setge','setl','setle','setna','setnae','setnb','setnbe',
			'setnc','setne','setng','setnge','setnl','setnle','setno','setnp','setns','setnz','seto','setp',
			'setpe','setpo','sets','setz','shld','shrd','stosd','bswap','cmpxchg','invd','invlpg','wbinvd','xadd','lock',
			'rep','repe','repne','repnz','repz'
		  ),
		/*FPU*/
		2 => array(
			  'f2xm1','fabs','fadd','faddp','fbld','fbstp','fchs','fclex','fcom','fcomp','fcompp','fdecstp',
			 'fdisi','fdiv','fdivp','fdivr','fdivrp','feni','ffree','fiadd','ficom','ficomp','fidiv',
   			 'fidivr','fild','fimul','fincstp','finit','fist','fistp','fisub','fisubr','fld','fld1',
			 'fldcw','fldenv','fldenvw','fldl2e','fldl2t','fldlg2','fldln2','fldpi','fldz','fmul',
			 'fmulp','fnclex','fndisi','fneni','fninit','fnop','fnsave','fnsavew','fnstcw','fnstenv',
			 'fnstenvw','fnstsw','fpatan','fprem','fptan','frndint','frstor','frstorw','fsave',
			 'fsavew','fscale','fsqrt','fst','fstcw','fstenv','fstenvw','fstp','fstsw','fsub','fsubp',
			 'fsubr','fsubrp','ftst','fwait','fxam','fxch','fxtract','fyl2x','fyl2xp1',
			 'fsetpm','fcos','fldenvd','fnsaved','fnstenvd','fprem1','frstord','fsaved','fsin','fsincos',
			 'fstenvd','fucom','fucomp','fucompp'
		    ),
		/*registers*/
		3 => array(
			'ah','al','ax','bh','bl','bp','bx','ch','cl','cr0','cr2','cr3','cs','cx','dh','di','dl',
			'dr0','dr1','dr2','dr3','dr6','dr7','ds','dx','eax','ebp','ebx','ecx','edi','edx',
			 'es','esi','esp','fs','gs','si','sp','ss','st','tr3','tr4','tr5','tr6','tr7', 'ah', 'bh', 'ch', 'dh'
			),
		/*Directive*/
		4 => array(
			  '186','286','286c','286p','287','386','386c','386p','387','486','486p',
			 '8086','8087','alpha','break','code','const','continue','cref','data','data?',
			'dosseg','else','elseif','endif','endw','err','err1','err2','errb',
			 'errdef','errdif','errdifi','erre','erridn','erridni','errnb','errndef',
			 'errnz','exit','fardata','fardata?','if','lall','lfcond','list','listall',
			 'listif','listmacro','listmacroall',' model','no87','nocref','nolist',
			 'nolistif','nolistmacro','radix','repeat','sall','seq','sfcond','stack',
			  'startup','tfcond','type','until','untilcxz','while','xall','xcref',
			  'xlist','alias','align','assume','catstr','comm','comment','db','dd','df','dosseg','dq',
			  'dt','dup','dw','echo','else','elseif','elseif1','elseif2','elseifb','elseifdef','elseifdif',
			  'elseifdifi','elseife','elseifidn','elseifidni','elseifnb','elseifndef','end',
			  'endif','endm','endp','ends','eq',' equ','even','exitm','extern','externdef','extrn','for',
			  'forc','ge','goto','group','high','highword','if','if1','if2','ifb','ifdef','ifdif',
			  'ifdifi','ife',' ifidn','ifidni','ifnb','ifndef','include','includelib','instr','invoke',
			  'irp','irpc','label','le','length','lengthof','local','low','lowword','lroffset',
			  'macro','mask','mod','msfloat','name','ne','offset','opattr','option','org','%out',
			  'page','popcontext','proc','proto','ptr','public','purge','pushcontext','record',
			  'repeat','rept','seg','segment','short','size','sizeof','sizestr','struc','struct',
			  'substr','subtitle','subttl','textequ','this','title','type','typedef','union','while','width',
			  '.model', '.stack', '.code', '.data'

		    ),

		/*Operands*/
		5 => array(
			 '@b','@f','addr','basic','byte','c','carry?','dword',
			 'far','far16','fortran','fword','near','near16','overflow?','parity?','pascal','qword',
			 'real4',' real8','real10','sbyte','sdword','sign?','stdcall','sword','syscall','tbyte',
			 'vararg','word','zero?','flat','near32','far32',
			 'abs','all','assumes','at','casemap','common','compact',
			 'cpu','dotname','emulator','epilogue','error','export','expr16','expr32','farstack','flat',
			 'forceframe','huge','language','large','listing','ljmp','loadds','m510','medium','memory',
			 'nearstack','nodotname','noemulator','nokeyword','noljmp','nom510','none','nonunique',
			 'nooldmacros','nooldstructs','noreadonly','noscoped','nosignextend','nothing',
			 'notpublic','oldmacros','oldstructs','os_dos','para','private','prologue','radix',
			 'readonly','req','scoped','setif2','smallstack','tiny','use16','use32','uses'
			)
		),
	'SYMBOLS' => array(
		'[', ']', '(', ')'
		),
	'CASE_SENSITIVE' => array(
		GESHI_COMMENTS => true,
		1 => false,
		2 => false,
		3 => false,
		4 => false,
		5 => false
		),
	'STYLES' => array(
		'KEYWORDS' => array(
			1 => 'color: #00007f;',
			2 => 'color: #0000ff;',
			3 => 'color: #46aa03; font-weight:bold;',
			4 => 'color: #0000ff;',
			5 => 'color: #0000ff;'
			),
		'COMMENTS' => array(
			1 => 'color: #adadad; font-style: italic;',
			),
		'ESCAPE_CHAR' => array(
			0 => 'color: #000099; font-weight: bold;'
			),
		'BRACKETS' => array(
			0 => 'color: #66cc66;'
			),
		'STRINGS' => array(
			0 => 'color: #7f007f;'
			),
		'NUMBERS' => array(
			0 => 'color: #ff0000;'
			),
		'METHODS' => array(
			),
		'SYMBOLS' => array(
			0 => 'color: #66cc66;'
			),
		'REGEXPS' => array(
			0 => 'color: #ff0000;',
			1 => 'color: #ff0000;'
			),
		'SCRIPT' => array(
			)
		),
	'URLS' => array(
		1 => '',
		2 => ''
		),
	'OOLANG' => false,
	'OBJECT_SPLITTERS' => array(
		),
	'REGEXPS' => array(
		0 => '0[0-9a-fA-F][0-9a-fA-F]*[hH]',
		1 => '[01][01]*[bB]'
		),
	'STRICT_MODE_APPLIES' => GESHI_NEVER,
	'SCRIPT_DELIMITERS' => array(
		),
	'HIGHLIGHT_STRICT_BLOCK' => array(
		)
);

?>
