function init()
{
  removeFocusBorders();
}

function removeFocusBorders()
{
  var tags = [ "img", "input", "a" ];
  for (var i=0; i<tags.length; i++) {
    var nodes = document.getElementsByTagName(tags[i]);
    for (var j=0; j<nodes.length; j++)
      if (tags[i] != "input" || (nodes[j].getAttribute("type") != "text" && nodes[j].getAttribute("type") != "password"))
        nodes[j].onfocus = function() { if(this.blur) this.blur(); }
  }
}