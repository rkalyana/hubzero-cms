if(typeof(Joomla)==='undefined'){var Joomla={}}Joomla.combobox={};Joomla.combobox.transform=function(el,options){el=$(el);var o=$('<option class="custom">').attr('text',Joomla.JText._('ComboBoxInitString','type custom...')).prependTo(el);el.attr('changeType','manual');el.on('keypress',function(e){if((this.options.selectedIndex!=0)&&(this.attr('changeType')=='auto')){this.options.selectedIndex=0;this.attr('changeType','manual')}if((e.code>47&&e.code<59)||(e.code>62&&e.code<127)||(e.code==32)){var validChar=true}else{var validChar=false}if(this.options.selectedIndex==0){var customString=this.options[0].value;if((validChar==true)||(e.key=='backspace')){if(customString==Joomla.JText._('ComboBoxInitString','type custom...')){customString=''}}if(e.key=='backspace'){customString=customString.substring(0,customString.length-1);if(customString==''){customString=Joomla.JText._('ComboBoxInitString','type custom...')}this.attr('changeType','manual')}if(validChar==true){customString+=String.fromCharCode(e.code)}this.options.selectedIndex=0;this.options[0].text=customString;this.options[0].value=customString;e.stop()}});el.on('change',function(e){if((this.options.selectedIndex!=0)&&(this.get('changeType')=='auto')){this.options.selectedIndex=0;this.attr('changeType','manual')}});el.on('keydown',function(e){if(e.code==8||e.code==127){e.stop()}if(this.options.selectedIndex==0){var character=String.fromCharCode(e.code).toLowerCase();for(var i=1;i<this.options.length;i++){var FirstChar=this.options[i].value.charAt(0).toLowerCase();if((FirstChar==character)){this.options.selectedIndex=0;this.set('changeType','auto')}}}});el.on('keyup',function(e){if((e.key=='left')||(e.key=='right')){this.options.selectedIndex=0}if((this.options.selectedIndex!=0)&&(this.attr('changeType')=='auto')){this.options.selectedIndex=0;this.attr('changeType','manual')}})};jQuery(document).ready(function($){$('select.combobox').each(function(i,el){Joomla.combobox.transform(el)})});