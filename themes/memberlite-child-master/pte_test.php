<?php

include('/var/www/html/proteamedge/public/wp-blog-header.php');
require('/var/www/html/proteamedge/public/wp-content/themes/memberlite-child-master/vendor/autoload.php');

$mpdf = new \Mpdf\Mpdf();

//==============================================================

$html = '
<style>

.pte_input{
	background-color: red;
}

textarea {
}
input {
}
select {
	font-family: Arial
}
</style>
<body>
<h2>Active Forms</h2>

<form action="formsubmit.php" method="post">
<b>Input Text</b>
<input type="text" size="90" class="pte_input" name="inputfield" value="" title="The title attribute works like a tool-tip"  />
<br /><br />

<b>Readonly</b>
<input id="pte_input" class="pte_input" type="text" size="90" name="readonlyfield" value="Read-only field. The value will be exported on submit" title="The title does not do anything if the field is read-only" readonly="readonly" />
<br /><br />

<b>Disabled</b>
<input type="text" size="90" name="disabledfield" value="Disabled field. The value will not be exported on submit" title="The title does not do anything if the field is disabled." disabled="disabled" />
<br /><br />

<b>Required</b>
<input type="text" size="90" name="requiredfield" value="" title="This field must be entered before you can submit." required="required" />
<br /><br />

<b>Hidden</b>
<input type="hidden" name="hiddenfield" value="value_of_hidden_field">
<br /><br />

<b>Input Password</b>
<input type="password" size="40" name="password" value="mysecretpassword" title="This field is set to act as a password field." >
<br /><br />

<b>Calculations</b>
<input type="text" size="20" name="maths1" value="0" onChange="var a = this.getField(\'maths1\').value;
var b = this.getField(\'maths2\').value;
this.getField(\'maths3\').value = a + b;
" /> +
<input type="text" size="20" name="maths2" value="0" onChange="var a = this.getField(\'maths1\').value;
var b = this.getField(\'maths2\').value;
this.getField(\'maths3\').value = a + b;
" /> =
<input type="text" size="20" name="maths3" value="" readonly="readonly"/>
<br /><br />


<b>Textarea</b>
<textarea name="textareafield" spellcheck="true" rows="5" cols="80" wrap="virtual" title="Textarea tooltip">Quisque viverra. Etiam id libero at magna pellentesque aliquet. Nulla sit amet ipsum id enim tempus dictum. Quisque viverra. Etiam id libero at magna pellentesque aliquet. Nulla sit amet ipsum id enim tempus dictum. Quisque viverra. Etiam id libero at magna pellentesque aliquet. Nulla sit amet ipsum id enim tempus dictum. Quisque viverra. Etiam id libero at magna pellentesque aliquet. Nulla sit amet ipsum id enim tempus dictum. Quisque viverra. Etiam id libero at magna pellentesque aliquet. Nulla sit amet ipsum id enim tempus dictum. Quisque viverra. Etiam id libero at magna pellentesque aliquet. Nulla sit amet ipsum id enim tempus dictum. Quisque viverra. Etiam id libero at magna pellentesque aliquet. Nulla sit amet ipsum id enim tempus dictum. Quisque viverra. Etiam id libero at magna pellentesque aliquet. Nulla sit amet ipsum id enim tempus dictum. </textarea>
<br /><br />


<b>First language</b>
<select name="first_language" editable="editable">
<option value="" >- Please select or type in -</option>
<option value="Arabic" >&#1575;&#1604;&#1593;&#1585;&#1576;&#1610;&#1577;</option>
<option value="Bulgarian" >&#1073;&#1098;&#1083;&#1075;&#1072;&#1088;&#1089;&#1082;&#1080; &#1077;&#1079;&#1080;&#1082;</option>
<option value="Chinese" >&#27721;&#35821;</option>
<option value="Czech" >&#269;esky</option>
<option value="English" >English</option>
<option value="French" >Fran&#231;ais</option>
<option value="Greek" >&#917;&#955;&#955;&#951;&#957;&#953;&#954;&#940;</option>
<option value="Japanese" >&#26085;&#26412;&#35486;</option>
<option value="Latvian" >Latvie&#353;u valoda</option>
<option value="Russian" >&#1056;&#1091;&#1089;&#1089;&#1082;&#1086;</option>
<option value="Spanish" >Espa&#241;ol</option>
<option value="Turkish" >T&#252;rk&#231;e</option>
</select>
&nbsp;&nbsp;&nbsp;&nbsp;

You have selected: <input type="text" size="20" name="first_language_display" value="" readonly="readonly" onCalculate="
var choice = this.getField(\'first_language\').value;
this.getField(\'first_language_display\').value = choice;
"/>

<br /><br />


<div>
<b>Spoken language(s)</b><br />
<select size="6" multiple="multiple" name="spoken_languages">
<option value="Arabic" >&#1575;&#1604;&#1593;&#1585;&#1576;&#1610;&#1577;</option>
<option value="Bulgarian" >&#1073;&#1098;&#1083;&#1075;&#1072;&#1088;&#1089;&#1082;&#1080; &#1077;&#1079;&#1080;&#1082;</option>
<option value="Chinese" >&#27721;&#35821;</option>
<option value="Czech" >&#269;esky</option>
<option value="English" selected="selected">English</option>
<option value="French" >Fran&#231;ais</option>
<option value="Greek" >&#917;&#955;&#955;&#951;&#957;&#953;&#954;&#940;</option>
<option value="Japanese" >&#26085;&#26412;&#35486;</option>
<option value="Latvian" >Latvie&#353;u valoda</option>
<option value="Russian" >&#1056;&#1091;&#1089;&#1089;&#1082;&#1086;</option>
<option value="Spanish" >Espa&#241;ol</option>
<option value="Turkish" >T&#252;rk&#231;e</option>
</select>
</div>
<br /><br />



<b>How many languages do you speak?</b> &nbsp;&nbsp;&nbsp;&nbsp;
<input type="radio" name="num_languages" value="1" checked="checked" > One &nbsp;&nbsp;&nbsp;&nbsp;
<input type="radio" name="num_languages" value="2" > Two &nbsp;&nbsp;&nbsp;&nbsp;
<input type="radio" name="num_languages" value="3" > Three &nbsp;&nbsp;&nbsp;&nbsp;
<input type="radio" name="num_languages" value="4" > Four &nbsp;&nbsp;&nbsp;&nbsp;
<input type="radio" name="num_languages" value="5" > Five +

<br /><br />


<input type="checkbox" name="email" value="email_subscrbe" checked="checked" /> Please send me regular information by e-mail<br>
<input type="checkbox" name="phone" value="phone-me" disabled="disabled" /> Please phone me (not available at present)<br>
<input type="checkbox" name="subscribe" value="newsletter_subscribe" /> Please subscribe me to the Newsletter
<br /><br />


<input type="submit" name="disabled_submit" value="Disabled" disabled="disabled" title="This Submit button is disabled" />

<input type="reset" name="resetfield" value="Reset" />

<input type="submit" name="submit" value="Submit" title="Click here to submit" />

<input type="submit" name="submit_in_Turkish" value="Submit (T&#252;rk&#231;e)" title="Click here to submit in T&#252;rk&#231;e" />

<input type="button" name="javascriptButton" value="Javascript Button" onClick="
var npass = this.getField(\'password\').value;


</form>
';



//==============================================================
$mpdf->useActiveForms = false;

$mpdf->WriteHTML($html);


$mpdf->Output(); exit;

