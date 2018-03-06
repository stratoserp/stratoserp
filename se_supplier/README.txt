
Need to make a bookmark for importing items, something like this for each supplier to make grabbing the new product easier.

<a href="

  javascript:
  function load(filename,type) {
    if (type=='js') {
      var fileref=document.createElement('script');
      fileref.setAttribute('type','text/javascript');
      fileref.setAttribute('src',filename);
    } else if (type=='css') {
      var fileref=document.createElement('link');
      fileref.setAttribute('rel','stylesheet');
      fileref.setAttribute('type','text/css');
      fileref.setAttribute('href',filename);
    }
    document.getElementsByTagName('head')[0].appendChild(fileref);
  }

  load('https://site/path.js','js');
  load('https://site/path.css','css');

">Import item</a>

Code borrowed from here: https://pynej.blogspot.com.au/2017/07/netflix-to-trakttv-sync.html