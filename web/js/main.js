function sfMediaLibrary_Engine()
{
  // Browser check
  var ua = navigator.userAgent;
  this.isMSIE = (navigator.appName == "Microsoft Internet Explorer");
  this.isMSIE5 = this.isMSIE && (ua.indexOf('MSIE 5') != -1);
  this.isMSIE5_0 = this.isMSIE && (ua.indexOf('MSIE 5.0') != -1);
  this.isGecko = ua.indexOf('Gecko') != -1;
  this.isSafari = ua.indexOf('Safari') != -1;
  this.isOpera = ua.indexOf('Opera') != -1;
  this.isMac = ua.indexOf('Mac') != -1;
  this.isNS7 = ua.indexOf('Netscape/7') != -1;
  this.isNS71 = ua.indexOf('Netscape/7.1') != -1;
  this.isTinyMCE = false;

  // Fake MSIE on Opera and if Opera fakes IE, Gecko or Safari cancel those
  if (this.isOpera) {
    this.isMSIE = true;
    this.isGecko = false;
    this.isSafari =  false;
  }
}

sfMediaLibrary_Engine.prototype = {
  init : function(url)
  {
    this.url = url;
  },

  fileBrowserReturn : function (url,PopUp)
  {
    if (PopUp)
    {
      var win =PopUp.getWindowArg("window");
      var input=PopUp.getWindowArg("input");
      win.document.getElementById(input).value=url;
      if (this.fileBrowserType == 'image')
      {
        if (win.ImageDialog.showPreviewImage)
        {
          win.ImageDialog.showPreviewImage(url);
        }
      }
      PopUp.close();
    }
    else
    {
      this.fileBrowserWin.document.forms[this.fileBrowserFormName].elements[this.fileBrowserFieldName].value = url;
    }
  },

  fileBrowserCallBack : function (field_name, url, type, win)
  {
    //Set TinyMCE to true
    this.isTinyMCE=true;
    //Store the URL
    var url = this.url;
    this.fileBrowserType = type;
    //Check the type of image
    if (type == 'image')
      url += '/images_only/1';
      tinyMCE.activeEditor.windowManager.open({
        file: url,
        title: "Please select a image",
        width: 640,
        height: 480,
        resizable: "yes",
        inline:    "yes",
        scrollbars: "yes",
        close_previous: "yes"
      },
      {
          window : win,
          input : field_name,
          mediaLibrary: this
      });
  },

  openWindow : function(options)
  {
    var width, height, x, y, resizable, scrollbars, url;

    if (!options)
      return;
    if (!options['field_name'])
      return;
    if (!options['url'] && !this.url)
      return;
    this.fileBrowserWin = self;
    this.fileBrowserFormName = (options['form_name'] == '') ? 0 : options['form_name'];
    this.fileBrowserFieldName = options['field_name'];
    this.fileBrowserType = options['type'];

    url = this.url;
    if (options['type'] == 'image')
      url += '/images_only/1';

    if (!(width = parseInt(options['width'])))
      width = 550;

    if (!(width = parseInt(options['width'])))
      width = 550;

    if (!(height = parseInt(options['height'])))
      height = 600;

    // Add to height in M$ due to SP2 WHY DON'T YOU GUYS IMPLEMENT innerWidth of windows!!
    if (sfMediaLibrary.isMSIE)
      height += 40;
    else
      height += 20;

    x = parseInt(screen.width / 2.0) - (width / 2.0);
    y = parseInt(screen.height / 2.0) - (height / 2.0);

    resizable = (options && options['resizable']) ? options['resizable'] : "no";
    scrollbars = (options && options['scrollbars']) ? options['scrollbars'] : "no";

    var modal = (resizable == "yes") ? "no" : "yes";

    if (sfMediaLibrary.isGecko && sfMediaLibrary.isMac)
      modal = "no";

    if (options['close_previous'] != "no")
      try {sfMediaLibrary.lastWindow.close();} catch (ex) {}

    var win = window.open(url, "sfPopup" + new Date().getTime(), "top=" + y + ",left=" + x + ",scrollbars=" + scrollbars + ",dialog=" + modal + ",minimizable=" + resizable + ",modal=" + modal + ",width=" + width + ",height=" + height + ",resizable=" + resizable);

    if (options['close_previous'] != "no")
      sfMediaLibrary.lastWindow = win;

    eval('try { win.resizeTo(width, height); } catch(e) { }');

    // Make it bigger if statusbar is forced
    if (sfMediaLibrary.isGecko)
    {
      if (win.document.defaultView.statusbar.visible)
        win.resizeBy(0, sfMediaLibrary.isMac ? 10 : 24);
    }

    win.focus();

  }
}

var SfMediaLibrary = sfMediaLibrary_Engine; // Compatiblity with gzip compressors
var sfMediaLibrary = new sfMediaLibrary_Engine();
