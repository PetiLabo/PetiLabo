!function(e){function t(t,a){this.element=t,this.options=e.extend({},i,a),this.init()}var a="SimpleChart",i={ChartType:"Line",xPadding:40,yPadding:40,topmargin:35,rightmargin:25,data:null,toolwidth:300,toolheight:300,axiscolor:"#333",font:"italic 10pt sans-serif",headerfontsize:"14px",axisfontsize:"12px",piefontsize:"13px",pielabelcolor:"#fff",pielabelpercentcolor:"#000",textAlign:"center",textcolor:"#E6E6E6",showlegends:!0,showpielables:!1,legendposition:"bottom",legendsize:"100",xaxislabel:null,yaxislabel:null,title:null,LegendTitle:"Légende",pieborderColor:"#fff",pieborderWidth:2};t.prototype={init:function(){{var t=this,a=t.options,i=e(t.element).addClass("SimpleChart").addClass(a.ChartType).append("<canvas class='SimpleChartcanvas'></canvas>").find("canvas").css({"float":"right"==a.legendposition||"left"==a.legendposition?"left":"","margin-top":a.topmargin,"margin-right":a.rightmargin});i[0].getContext("2d")}i[0].width=e(t.element).width()-(a.showlegends&&("right"==a.legendposition||"left"==a.legendposition)?parseInt(a.legendsize)+parseInt(a.xPadding):0)-a.rightmargin,i[0].height=e(t.element).height()-(a.showlegends&&("bottom"==a.legendposition||"top"==a.legendposition)?a.legendsize:0)-a.topmargin;var l=i[0].getContext("2d");switch(a.ChartType){case"Line":t.drawAxis(l,i),t.drawLineAreaScatteredHybridCharts(l,i);break;case"Area":t.drawAxis(l,i),t.drawLineAreaScatteredHybridCharts(l,i);break;case"Scattered":t.drawAxis(l,i),t.drawLineAreaScatteredHybridCharts(l,i);break;case"Hybrid":t.drawAxis(l,i),t.drawLineAreaScatteredHybridCharts(l,i),t.drawBar(l,i),t.drawHybrid(l,i);break;case"Bar":t.drawAxis(l,i),t.drawBar(l,i);break;case"Pie":t.drawPie(l,i);break;case"Stacked":t.drawAxis(l,i),t.drawStacked(l,i);break;case"StackedHybrid":t.drawAxis(l,i),t.drawStacked(l,i),t.drawLineAreaScatteredHybridCharts(l,i)}a.showlegends&&t.drawLegends(i)},reload:function(){e(this.element).empty(),this.init()},destroy:function(){e(this.element).empty()},FindYMax:function(){config=this.options;for(var e=0,t=0;t<config.data.length;t++)for(var a=0;a<config.data[t].values.length;a++)config.data[t].values[a].Y>e&&(e=config.data[t].values[a].Y);return e+=5-e%5},pixelX:function(t,a){config=this.options;var i=e(this.element).find(".SimpleChartcanvas");return(i.width()-config.xPadding)/config.data[a].values.length*t+1.25*config.xPadding},pixelY:function(t){config=this.options;var a=e(this.element).find(".SimpleChartcanvas");return a.height()-(a.height()-config.yPadding-8)/this.FindYMax()*t-config.yPadding},getRandomColor:function(){for(var e="0123456789ABCDEF".split(""),t="#",a=0;6>a;a++)t+=e[Math.floor(16*Math.random())];return t},drawAxis:function(t,a){var i=this,l=new Array,n=this.options;t.lineWidth=2,t.strokeStyle=n.axiscolor,t.font=n.font,t.textAlign=n.textAlign,t.beginPath(),t.moveTo(n.xPadding,0),t.lineTo(n.xPadding,a.height()-n.yPadding),t.lineTo(a.width(),a.height()-n.yPadding),t.stroke(),t.fillStyle=n.textcolor;for(var o=0;o<n.data.length;o++)for(var d=0;d<n.data[o].values.length;d++)l.indexOf(n.data[o].values[d].X)<0&&(l.push(n.data[o].values[d].X),d%7==0&&t.fillText(n.data[o].values[d].X,i.pixelX(d,o),a.height()-n.yPadding+20),t.beginPath(),t.moveTo(i.pixelX(d,o),a.height()-n.yPadding-3),t.lineTo(i.pixelX(d,o),a.height()-n.yPadding+3),t.stroke());t.save();var r=t.font.split(" ");t.font=n.axisfontsize+" "+r[r.length-1],n.xaxislabel&&t.fillText(n.xaxislabel,a.width()/2,a.height()),n.yaxislabel&&(t.save(),t.translate(0,a.height()/2),t.rotate(-Math.PI/2),t.fillText(n.yaxislabel,0,15),t.restore()),n.title&&e("<div class='simple-chart-Header' />").appendTo(e(i.element)).html(n.title).css({left:a.width()/2-e(i.element).find(".simple-chart-Header").width()/2,top:5}),t.restore(),t.textAlign="right",t.textBaseline="middle";var s=i.FindYMax();incrementvalue=parseInt(s/5);for(var o=0;s>=o;o+=incrementvalue)t.fillStyle=n.textcolor,t.fillText(o,n.xPadding-10,i.pixelY(o)),o>0&&(t.save(),t.beginPath(),t.moveTo(n.xPadding,i.pixelY(o)),t.lineTo(a.width(),i.pixelY(o)),t.lineWidth=1,t.strokeStyle="#bbbbbb",t.setLineDash([2,5]),t.stroke(),t.restore(),t.beginPath(),t.moveTo(n.xPadding-3,i.pixelY(o)),t.lineTo(n.xPadding+3,i.pixelY(o)),t.lineWidth=2,t.strokeStyle=n.axiscolor,t.stroke())},drawPie:function(t,a){var i=this,l=this.options;t.clearRect(0,0,a.width(),a.height());for(var n=0,o=0,d=0;d<l.data[0].values.length;d++)n+="number"==typeof l.data[0].values[d].Y?l.data[0].values[d].Y:0;for(var r=0;r<l.data[0].values.length;r++){t.fillStyle="Random"==l.data[0].linecolor?l.data[0].values[r].color=randomcolor=i.getRandomColor():l.data[0].linecolor,t.beginPath();var s=a.width()/2.2,p=a.height()/2.2;if(t.moveTo(s,p),t.arc(s,p,"right"==l.legendposition||"left"==l.legendposition?s:p,o,o+2*Math.PI*(l.data[0].values[r].Y/n),!1),t.lineTo(s,p),t.fill(),t.fillStyle=l.pielabelcolor,t.lineWidth=l.pieborderWidth,t.strokeStyle=l.pieborderColor,t.stroke(),l.showpielables){t.save(),t.translate(s,p),t.rotate(o-.2+2*Math.PI*(l.data[0].values[r].Y/n));var h=Math.floor(.5*s)+40,g=Math.floor(.05*p);t.textAlign="right";var c=t.font.split(" ");t.font=l.piefontsize+" "+c[c.length-1],t.fillText(l.data[0].values[r].X,h,g),t.restore(),t.save(),t.fillStyle=l.pielabelpercentcolor,t.translate(s,p),t.rotate(o-.15+2*Math.PI*(l.data[0].values[r].Y/n));var h=Math.floor(.5*s)+90,g=Math.floor(.05*p);t.textAlign="right";var c=t.font.split(" ");t.font=l.piefontsize+" "+c[c.length-1],t.fillText(Math.round(l.data[0].values[r].Y/n*100)+"%",h,g),t.restore()}o+=2*Math.PI*(l.data[0].values[r].Y/n)}{var f=e(a).offset();f.left,f.top}},drawBar:function(e,t){for(var a=this,i=this.options,l=0;l<i.data[0].values.length;l++){var n;e.strokeStyle="Random"==i.data[0].linecolor?i.data[0].values[l].color=n=a.getRandomColor():i.data[0].linecolor,e.fillStyle="Random"==i.data[0].linecolor?n:i.data[0].linecolor,e.beginPath(),e.rect(a.pixelX(l,0)-i.yPadding/4,a.pixelY(i.data[0].values[l].Y),i.yPadding/2,t.height()-a.pixelY(i.data[0].values[l].Y)-i.xPadding+8),e.closePath(),e.stroke(),e.fill(),e.textAlign="left",e.fillStyle="#000",e.fillText(i.data[0].values[l].Y,a.pixelX(l,0)-i.yPadding/4,a.pixelY(i.data[0].values[l].Y)+7,200)}},drawStacked:function(e,t){for(var a=this,i=this.options,l=0;l<i.data.length;l++)for(var n=0;n<i.data[l].values.length;n++){var o;e.strokeStyle="Random"==i.data[l].linecolor?i.data[l].values[n].color=o=a.getRandomColor():i.data[l].linecolor,e.fillStyle="Random"==i.data[l].linecolor?o:i.data[l].linecolor,e.beginPath(),e.rect(a.pixelX(n,0)-i.yPadding/4,a.pixelY(i.data[l].values[n].Y),i.yPadding/2,t.height()-a.pixelY(i.data[l].values[n].Y)-i.xPadding+8),e.closePath(),e.stroke(),e.fill(),e.textAlign="left",e.fillStyle="#000",e.fillText(i.data[l].values[n].Y,a.pixelX(n,0)-i.yPadding/4,a.pixelY(i.data[l].values[n].Y)+7,200)}},drawHybrid:function(e){var t,a=this,i=this.options;e.strokeStyle="Random"==i.data[0].linecolor?t=a.getRandomColor():i.data[0].linecolor,e.beginPath(),e.moveTo(a.pixelX(0,0),a.pixelY(i.data[0].values[0].Y));for(var l=1;l<i.data[0].values.length;l++)e.lineTo(a.pixelX(l,0),a.pixelY(i.data[0].values[l].Y));e.stroke(),e.fillStyle="Random"==i.data[0].linecolor?t:i.data[0].linecolor;for(var l=0;l<i.data[0].values.length;l++)e.beginPath(),e.arc(a.pixelX(l,0),a.pixelY(i.data[0].values[l].Y),4,0,2*Math.PI,!0),e.fill()},drawLineAreaScatteredHybridCharts:function(t,a){function i(e){mouseX=parseInt(e.pageX-g),mouseY=parseInt(e.pageY-c);for(var t=!1,a=0;a<x.length;a++){var i=x[a],l=mouseX-i.x,h=mouseY-i.y+3,f=parseInt(i.tip),v=2>f?n.title.slice(0,-1):n.title;l*l+h*h<i.rXr&&(o[0].style.left=i.x-o[0].width/2-3+"px",o[0].style.top=i.y-21-o[0].height+n.topmargin+"px",d.clearRect(0,0,o[0].width,o[0].height),d.fillText(i.tipX,4,15),d.fillText(f+" "+v.toLowerCase(),3,30),p[0].style.left=i.x-7+"px",p[0].style.top=i.y+n.topmargin-19+"px",("Line"==n.ChartType||"Scattered"==n.ChartType||"Hybrid"==n.ChartType||"StackedHybrid"==n.ChartType)&&(r[0].style.left=i.x-9+"px",r[0].style.top=i.y+n.topmargin-9+"px"),s.clearRect(0,0,r.width(),r.height()),s.strokeStyle=i.color,s.beginPath(),s.arc(9,9,7,0,2*Math.PI),s.lineWidth=2,s.stroke(),t=!0)}t||(o[0].style.left="-400px",r[0].style.left="-400px",p[0].style.left="-400px")}var l=this,n=this.options,o=e(l.element).append("<canvas id='tip'></canvas><div class='down-triangle'></div>").find("#tip").attr("width",n.toolwidth).attr("height",n.toolheight),d=o[0].getContext("2d"),r=e(l.element).append("<canvas id='highlighter'></canvas>").find("#highlighter").attr("width","18").attr("height","18"),s=r[0].getContext("2d"),p=e(l.element).find(".down-triangle"),h=e(a).offset(),g=h.left,c=h.top;e(a[0]).on("mousemove",function(e){i(e)});for(var f=0;f<n.data.length;f++){if(t.strokeStyle="Random"==n.data[f].linecolor?n.data[f].Randomlinecolor=l.getRandomColor():n.data[f].linecolor,t.beginPath(),t.moveTo(l.pixelX(0,f),l.pixelY(n.data[f].values[0].Y)),"Scattered"!==n.ChartType&&"Hybrid"!==n.ChartType){for(var v=1;v<n.data[f].values.length;v++)t.lineTo(l.pixelX(v,f),l.pixelY(n.data[f].values[v].Y));t.stroke()}if(t.fillStyle="Random"==n.data[f].linecolor?n.data[f].Randomlinecolor:n.data[f].linecolor,"Area"==n.ChartType&&(t.lineTo(l.pixelX(n.data[f].values.length-1,f),l.pixelY(0)),t.lineTo(l.pixelX(0,0),l.pixelY(0)),t.stroke(),t.fill()),"Line"==n.ChartType||"Scattered"==n.ChartType||"StackedHybrid"==n.ChartType)for(var v=0;v<n.data[f].values.length;v++)t.beginPath(),t.arc(l.pixelX(v,f),l.pixelY(n.data[f].values[v].Y),4,0,2*Math.PI,!0),t.fill()}for(var x=[],f=0;f<n.data.length;f++)for(var v=0;v<n.data[f].values.length;v++)x.push({x:l.pixelX(v,f),y:l.pixelY(n.data[f].values[v].Y),r:4,rXr:25,tipX:n.data[f].values[v].X,tip:n.data[f].values[v].Y,color:"Random"==n.data[f].linecolor?n.data[f].Randomlinecolor:n.data[f].linecolor})},drawLegends:function(t){var a=this,i=this.options;if("Line"==i.ChartType||"Area"==i.ChartType||"Scattered"==i.ChartType||"Stacked"==i.ChartType||"StackedHybrid"==i.ChartType){for(var l=e("<div class='simple-chart-legends' />",{id:"legendsdiv"}).css({width:"right"==i.legendposition||"left"==i.legendposition?i.legendsize-5:t.width(),height:"top"==i.legendposition||"bottom"==i.legendposition?i.legendsize-5:t.height(),"float":"right"==i.legendposition||"left"==i.legendposition?"left":""}).appendTo(e(a.element)),n=e(l).append("<span>"+i.LegendTitle+"</span>").append("<ul />").find("ul"),o=0;o<i.data.length;o++)e("<li />",{"class":"legendsli"}).append("<span />").find("span").addClass("legendindicator").append('<span class="line" style="background: '+("Random"==i.data[o].linecolor?i.data[o].Randomlinecolor:i.data[o].linecolor)+'"></span><span class="circle" style="background: '+("Random"==i.data[o].linecolor?i.data[o].Randomlinecolor:i.data[o].linecolor)+'"></span>').parent().append("<span>"+i.data[o].title+"</span>").appendTo(n);("top"==i.legendposition||"left"==i.legendposition)&&e(l).insertBefore(e(a.element).find(".SimpleChartcanvas")),e(l).addClass("right"==i.legendposition||"left"==i.legendposition?"vertical":"horizontal")}if("Bar"==i.ChartType||"Hybrid"==i.ChartType||"Pie"==i.ChartType){for(var l=e("<div class='simple-chart-legends' />",{id:"legendsdiv"}).css({width:"right"==i.legendposition||"left"==i.legendposition?i.legendsize-5:t.width(),height:"top"==i.legendposition||"bottom"==i.legendposition?i.legendsize-5:t.height(),"float":"right"==i.legendposition||"left"==i.legendposition?"left":""}).appendTo(e(a.element)),n=e(l).append("<span>"+i.LegendTitle+"</span>").append("<ul />").find("ul"),o=0;o<i.data[0].values.length;o++)e("<li />",{"class":"legendsli"}).append("<span />").find("span").addClass("legendindicator").append('<span class="line" style="background: '+("Random"==i.data[0].linecolor?i.data[0].values[o].color:i.data[0].linecolor)+'"></span><span class="circle" style="background: '+("Random"==i.data[0].linecolor?i.data[0].values[o].color:i.data[0].linecolor)+'"></span>').parent().append("<span>"+i.data[0].values[o].X+"</span><span class='legendvalue'>"+("Pie"==i.ChartType?i.data[0].values[o].Y:"")+"</span>").appendTo(n);("top"==i.legendposition||"left"==i.legendposition)&&e(l).insertBefore(e(a.element).find(".SimpleChartcanvas")),e(l).addClass("right"==i.legendposition||"left"==i.legendposition?"vertical":"horizontal")}}},e.fn[a]=function(i){if("string"!=typeof i)return this.each(function(){e.data(this,"plugin_"+a)||e.data(this,"plugin_"+a,new t(this,i))});var l=Array.prototype.slice.call(arguments,1);this.each(function(){var t=e.data(this,"plugin_"+a);t[i]?t[i].apply(t,l):t.options[i]=l[0]})}}(jQuery,window,document,void 0);