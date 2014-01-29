/*
 * transform: A jQuery cssHooks adding cross-browser 2d transform capabilities to $.fn.css() and $.fn.animate()
 *
 * limitations:
 * - requires jQuery 1.4.3+
 * - Should you use the *translate* property, then your elements need to be absolutely positionned in a relatively positionned wrapper **or it will fail in IE678**.
 * - transformOrigin is not accessible
 *
 * latest version and complete README available on Github:
 * https://github.com/louisremi/jquery.transform.js
 *
 * Copyright 2011 @louis_remi
 * Licensed under the MIT license.
 *
 * This saved you an hour of work?
 * Send me music http://www.amazon.co.uk/wishlist/HNTU0468LQON
 *
 */
 (function(F,N,j,x,q){var Z=j.createElement("div"),B=Z.style,ab="transform",U="Transform",v=[ab,"O"+U,"ms"+U,"Webkit"+U,"Moz"+U],h="Origin",H=ab+"-origin",Q=ab+h,Y=v.length,w,e,f,a="Float32Array" in N,m,p,c,A,k,s=/Matrix([^)]*)/,I=/^\s*matrix\(\s*1\s*,\s*0\s*,\s*0\s*,\s*1\s*(?:,\s*0(?:px)?\s*){2}\)\s*$/,L=/^([\+\-]=)?(-?[\d+\.\-]+)([a-z]+|%)?(.*?)$/i,T=/%/,W=parseFloat,J="relative",aa="static",o="position",O="translate",l="rotate",G="scale",E="skew",S="matrix";while(Y--){if(v[Y] in B){F.support[ab]=w=v[Y];continue}}e=w+h;if(!w){F.support.matrixFilter=f=B.filter===""}F.cssNumber[ab]=true;F.cssNumber[Q]=true;if(w&&w!=ab){F.cssProps[ab]=w;F.cssProps[Q]=e;if(w=="Moz"+U){m={get:function(ae,i){return(i?F.css(ae,w).split("px").join(""):ae.style[w])},set:function(i,ae){i.style[w]=/matrix\([^)p]*\)/.test(ae)?ae.replace(/matrix((?:[^,]*,){4})([^,]*),([^)]*)/,S+"$1$2px,$3px"):ae}}}else{if(/^1\.[0-5](?:\.|$)/.test(F.fn.jquery)){m={get:function(ae,i){return(i?F.css(ae,w.replace(/^ms/,"Ms")):ae.style[w])}}}}}else{if(f){var R=U+"-translate-x",P=U+"-translate-y";m={get:function(ah,ag){var af=F(ah),ae=(ag&&ah.currentStyle?ah.currentStyle:ah.style),i;if(ae&&s.test(ae.filter)){i=RegExp.$1.split(",");i=[i[0].split("=")[1],i[2].split("=")[1],i[1].split("=")[1],i[3].split("=")[1]]}else{i=[1,0,0,1]}i[4]=af.data(R)||0;i[5]=af.data(P)||0;return S+"("+i+")"},set:function(aj,al,ag){var ah=F(aj),ak=aj.style,af,i,ae,ai;if(!ag){ak.zoom=1}al=C(al);i=["Matrix(M11="+al[0],"M12="+al[2],"M21="+al[1],"M22="+al[3],"SizingMethod='auto expand'"].join();ae=(af=aj.currentStyle)&&af.filter||ak.filter||"";ak.filter=s.test(ae)?ae.replace(s,i):ae+" progid:DXImageTransform.Microsoft."+i+")";ah.data(R,al[4]);ah.data(P,al[5]);k(aj)}};c={get:function(ak,aj){var ah=F(ak),ag=ah.data(H);if(!ag){var af=["-ms-"+H,H],ai=af.length,ae=ak.currentStyle;while(ai--){if(af[ai] in ae){ag=ae[af[ai]];ah.data(H,ag);break}}}if(!ag){ag="50% 50%";ah.data(H,ag)}return ag},set:function(aC,aw){var ag=F(aC),aq=p(aC);if(aw!==q){ag.data(H,aw)}if(!aq){return}aq=C(aq);var aG=aq[4]||ag.data(R)||0,aF=aq[5]||ag.data(P)||0,aH=D(aw===q?A(aC):aw).split(" ");var an=z(aq,1,1),av=ag.outerWidth()/an.width,at=ag.outerHeight()/an.height,az=2,ah;while(az--){ah=aH[az].match(L);if(ah[3]!=="px"){aH[az]=ah[3]==="%"?g(aH[az],aC,az,an,av,at):u(aH[az],aC)}else{aH[az]=W(aH[az])}}var ai=M(aq,aH[0],aH[1]),aB=M(aq,0,0),am={top:aB[1]-(ai[1]-aH[1]),left:aB[0]-(ai[0]-aH[0])},aD=d(aq,av,at);var ao=ag.css(o),af=ao===J||ao===aa||F.transform.centerOrigin===o,ap={},aA=af?"top":"marginTop",ay=af?"left":"marginLeft",ar=am.top+aF+aD.top,al=am.left+aG+aD.left,ae=0,au=0,ak,ax,aE=aC.style,aj=aC.currentStyle;if(ao===aa){ap[o]=J}else{aE[aA]=null;aE[ay]=null;ak=aj[aA];ax=aj[ay];if(ak!=="auto"){ae=parseInt(ak,10)}if(ax!=="auto"){au=parseInt(ax,10)}}ap[aA]=ar+ae;ap[ay]=al+au;ag.css(ap)}}}}if(m){F.cssHooks[ab]=m}if(c){F.cssHooks[Q]=c}p=m&&m.get||F.css;A=c&&c.get||F.css;k=c&&c.set||F.css;F.fx.step.transform=function(ai){var ah=ai.elem,af=ai.start,aj=ai.end,am=ai.pos,ag="",ak,ae,al,an;if(!af||typeof af==="string"){if(!af){af=p(ah,w)}if(f){ah.style.zoom=1}aj=aj.split("+=").join(af);return F.extend(ai,t(af,aj))}ak=af.length;while(ak--){ae=af[ak];al=aj[ak];an=+false;switch(ae[0]){case O:an="px";case G:an||(an=" ");case E:an||(an="rad");ag=ae[0]+"("+(ae[1][0]+(al[1][0]-ae[1][0])*am)+an+","+(ae[1][1]+(al[1][1]-ae[1][1])*am)+an+")"+ag;break;case l:ag=l+"("+(ae[1]+(al[1]-ae[1])*am)+"rad)"+ag;break}}ai.origin&&(ag=ai.origin+ag);m&&m.set?m.set(ah,ag,+true):ah.style[w]=ag};F.fx.step.transformOrigin=function(ah){var ag=ah.elem,af,an=[],am=ah.pos,ai=2,aj,ao=[],ae,ak,al;if(!ah.state){ae=D(A(ag,e)).split(" ");ak=D(ah.end).split(" ");while(ai--){aj=ak[ai].match(L)[1];if(f){al=z(C(p(ag)),1,1)}ae[ai]=r(ae[ai],ag,ai,al);ak[ai]=r(ak[ai],ag,ai,al);if(aj){ak[ai]=ae[ai]+(aj==="+="?1:-1)*ak[ai]}}ai=2;ah.start=ae;ah.end=ak;ah.unit="px"}af=ah.start;while(ai--){an[ai]=(af[ai]+(ah.end[ai]-af[ai])*am)+ah.unit}an=an.join(" ");f?k(ag,an):ag.style[e]=an};function r(ah,af,ae,i){var ag=ah.match(L);ah=ag[2]+ag[3];if(ag[3]!=="px"){ah=ag[3]==="%"?g(ah,af,ae,i):u(ah,af)}else{ah=W(ah)}return ah}function D(am){var ao="top",ae="right",aj="bottom",ak="center",ap="left",ah=" ",an="0",ag="50%",af="100%",al,ai=2;switch(am){case ao+ah+ap:case ap+ah+ao:am=an+ah+an;break;case ao:case ao+ah+ak:case ak+ah+ao:am=ag+ah+an;break;case ae+ah+ao:case ao+ah+ae:am=af+ah+an;break;case ap:case ap+ah+ak:case ak+ah+ap:am=an+ah+ag;break;case ae:case ae+ah+ak:case ak+ah+ae:am=af+ah+ag;break;case aj+ah+ap:case ap+ah+aj:am=an+ah+af;break;case aj:case aj+ah+ak:case ak+ah+aj:am=ag+ah+af;break;case aj+ah+ae:case ae+ah+aj:am=af+ah+af;break;case ak:case ak+ah+ak:am=ag+ah+ag;break;default:al=am.split(ah);if(al[1]===q){al[1]=al[0]}while(ai--){switch(al[ai]){case ap:case ao:al[ai]=an;break;case ae:case aj:al[ai]=af;break;case ak:al[ai]=ag}}am=al.join(ah)}return am}function M(ae,i,af){return[ae[0]*i+ae[2]*af,ae[1]*i+ae[3]*af]}function ad(ae,i,af){return[M(ae,0,0),M(ae,0,af),M(ae,i,0),M(ae,i,af)]}function d(ae,i,ag){var af=ad(ae,i,ag);return{top:x.min(af[0][1],af[2][1],af[3][1],af[1][1]),bottom:x.max(af[0][1],af[2][1],af[3][1],af[1][1]),left:x.min(af[0][0],af[2][0],af[3][0],af[1][0]),right:x.max(af[0][0],af[2][0],af[3][0],af[1][0])}}function z(ae,i,ag){var af=d(ae,i,ag);return{height:x.abs(af.bottom-af.top),width:x.abs(af.right-af.left)}}function C(ag){ag=ag.split(")");var ah=F.trim,ak=-1,aj=ag.length-1,am,ae,af,ai=a?new Float32Array(6):[],an=a?new Float32Array(6):[],al=a?new Float32Array(6):[1,0,0,1,0,0];ai[0]=ai[3]=al[0]=al[3]=1;ai[1]=ai[2]=ai[4]=ai[5]=0;while(++ak<aj){am=ag[ak].split("(");ae=ah(am[0]);af=am[1];an[0]=an[3]=1;an[1]=an[2]=an[4]=an[5]=0;switch(ae){case O+"X":an[4]=parseInt(af,10);break;case O+"Y":an[5]=parseInt(af,10);break;case O:af=af.split(",");an[4]=parseInt(af[0],10);an[5]=parseInt(af[1]||0,10);break;case l:af=n(af);an[0]=x.cos(af);an[1]=x.sin(af);an[2]=-an[1];an[3]=an[0];break;case G+"X":an[0]=+af;break;case G+"Y":an[3]=af;break;case G:af=af.split(",");an[0]=af[0];an[3]=af.length>1?af[1]:af[0];break;case E+"X":an[2]=x.tan(n(af));break;case E+"Y":an[1]=x.tan(n(af));break;case E:af=af.split(",");an[2]=x.tan(n(af[0]));af[1]&&(an[1]=x.tan(n(af[1])));break;case S:af=af.split(",");an[0]=af[0];an[1]=af[1];an[2]=af[2];an[3]=af[3];an[4]=parseInt(af[4],10);an[5]=parseInt(af[5],10);break}al[0]=ai[0]*an[0]+ai[2]*an[1];al[1]=ai[1]*an[0]+ai[3]*an[1];al[2]=ai[0]*an[2]+ai[2]*an[3];al[3]=ai[1]*an[2]+ai[3]*an[3];al[4]=ai[0]*an[4]+ai[2]*an[5]+ai[4];al[5]=ai[1]*an[4]+ai[3]*an[5]+ai[5];ai=[al[0],al[1],al[2],al[3],al[4],al[5]]}return al}function b(ag){var ah,af,ae,i=ag[0],ak=ag[1],aj=ag[2],ai=ag[3];if(i*ai-ak*aj){ah=x.sqrt(i*i+ak*ak);i/=ah;ak/=ah;ae=i*aj+ak*ai;aj-=i*ae;ai-=ak*ae;af=x.sqrt(aj*aj+ai*ai);aj/=af;ai/=af;ae/=af;if(i*ai<ak*aj){i=-i;ak=-ak;ae=-ae;ah=-ah}}else{throw new Error("matrix is singular")}return[[O,[+ag[4],+ag[5]]],[l,x.atan2(ak,i)],[E,[x.atan(ae),0]],[G,[ah,af]]]}function t(al,af){var ai={start:[],end:[]},ag=-1,ae,ah,aj,ak;(al=="none"||X(al))&&(al="");(af=="none"||X(af))&&(af="");if(al&&af&&!af.indexOf("matrix")&&y(al).join()==y(af.split(")")[0]).join()){ai.origin=al;al="";af=af.slice(af.indexOf(")")+1)}if(!al&&!af){return}if(!al||!af||V(al)==V(af)){al&&(al=al.split(")"))&&(ae=al.length);af&&(af=af.split(")"))&&(ae=af.length);while(++ag<ae-1){al[ag]&&(ah=al[ag].split("("));af[ag]&&(aj=af[ag].split("("));ak=F.trim((ah||aj)[0]);K(ai.start,ac(ak,ah?ah[1]:0));K(ai.end,ac(ak,aj?aj[1]:0))}}else{ai.start=b(C(al));ai.end=b(C(af))}return ai}function ac(ag,ah){var ae=+(!ag.indexOf(G)),ai=!ag.indexOf(E)?n:W,af,i=ag.replace(/[XY]/,"");switch(ag){case O+"Y":case G+"Y":case E+"Y":ah=[ae,ah?ai(ah):ae];break;case O+"X":case O:case G+"X":af=1;case G:case E+"X":case E:ah=ah?(ah=ah.split(","))&&[ai(ah[0]),ai(ah.length>1?ah[1]:ag==G?af||ah[0]:ae+"")]:[ae,ae];break;case l:ah=ah?n(ah):0;break;case S:return b(ah?y(ah):[1,0,0,1,0,0]);break}return[[i,ah]]}function X(i){return I.test(i)}function V(i){return i.replace(/(?:\([^)]*\))|\s/g,"")}function K(ae,i,af){while(af=i.shift()){ae.push(af)}}function n(i){var af=W(i),ae=x.PI;return ~i.indexOf("deg")?af*(ae/180):~i.indexOf("grad")?af*(ae/200):~i.indexOf("turn")?af*(ae/0.5):af}function u(ag,af,aj){aj=aj||"left";var ae=af.style[aj],i=ae!==q&&ae!==null,ah=F.css(af,aj),ai;F.style(af,aj,ag);ai=F.css(af,aj);i?F.style(this,aj,ah):af.style[aj]=null;return W(ai)}function g(aj,af,ai,ah,i,ak){var ag=1,ae=F(af),al=(ai?ak:i)||ae["outer"+(ai?"Height":"Width")]();if(f){ag=ah[(ai?"height":"width")]}aj=al*W(aj)/100/ag;return aj}function y(i){i=/([^,]*),([^,]*),([^,]*),([^,]*),([^,p]*)(?:px)?,([^)p]*)(?:px)?/.exec(i);return[i[1],i[2],i[3],i[4],i[5],i[6]]}F.transform={centerOrigin:o}})(jQuery,window,document,Math);// -----------------------------------------------------------------------------------
// http://wowslider.com/
// JavaScript Wow Slider is a free software that helps you easily generate delicious 
// slideshows with gorgeous transition effects, in a few clicks without writing a single line of code.
// Generated by WOW Slider 4.2
//
//***********************************************
// Obfuscated by Javascript Obfuscator
// http://javascript-source.com
//***********************************************
function ws_page(d,b,a){var c=$("<div/>").css({position:"absolute",width:"100%",height:"100%",top:"0%",overflow:"hidden"});var e=a.find("ul");c.hide().appendTo(a);this.go=function(h,j){function g(){c.find("div").stop(1,1);c.hide();c.empty()}g();e.hide();var f=$("<div/>").css({position:"absolute",left:"50%",top:"50%",width:"0%",height:"0%",overflow:"hidden","z-index":9,opacity:0.2}).append($(b.get(h)).clone().css({width:"100%",height:"100%"})).appendTo(c);var i=$("<div/>").css({position:"absolute",left:0,top:0,width:"100%",height:"100%",overflow:"hidden","z-index":10,"transform-origin":"top left"}).append($(b.get(j)).clone().css({width:"100%",height:"100%"})).appendTo(c);c.show();i.animate({transform:"rotate(17deg)"},{duration:2*d.duration/3,easing:"easeOutBounce"}).animate({top:"100%"},{duration:d.duration/3,easing:"easeInQuart",complete:function(){g();e.css("left",-h*100+"%").show()}});f.animate({opacity:1,top:0,left:0,width:"100%",height:"100%"},{duration:3*d.duration/4});return h}}jQuery.extend(jQuery.easing,{easeInQuart:function(e,f,a,h,g){return h*(f/=g)*f*f*f+a},easeOutBounce:function(e,f,a,h,g){if((f/=g)<(1/2.75)){return h*(7.5625*f*f)+a}else{if(f<(2/2.75)){return h*(7.5625*(f-=(1.5/2.75))*f+0.75)+a}else{if(f<(2.5/2.75)){return h*(7.5625*(f-=(2.25/2.75))*f+0.9375)+a}else{return h*(7.5625*(f-=(2.625/2.75))*f+0.984375)+a}}}}});// -----------------------------------------------------------------------------------
// http://wowslider.com/
// JavaScript Wow Slider is a free software that helps you easily generate delicious 
// slideshows with gorgeous transition effects, in a few clicks without writing a single line of code.
// Generated by WOW Slider 4.2
//
//***********************************************
// Obfuscated by Javascript Obfuscator
// http://javascript-source.com
//***********************************************
jQuery("#wowslider-container1").wowSlider({effect:"page",prev:"",next:"",duration:20*100,delay:20*100,width:320,height:240,autoPlay:true,playPause:true,stopOnHover:false,loop:false,bullets:1,caption:true,captionEffect:"slide",controls:true,onBeforeStep:0,images:0});