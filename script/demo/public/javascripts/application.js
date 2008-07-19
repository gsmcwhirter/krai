var CONFIG = {
  "BASEURI": "/"
};

function AddNotice(text)
{
  $('#messages').append('<span class="notice">'+text+'</span><br />');
  $('#messages').show();
}

function AddError(text)
{
  $('#messages').append('<span class="error">'+text+'</span><br />');
  $('#messages').show();
}

function linkTargets(relval, atarget)
{
  $('a[href][rel=\''+relval+'\']').attr("target",atarget);
}

function disable_button(button, newtext)
{
  $("#"+button).val(newtext);
  $("#"+button).attr("disabled", "disabled");
}

function page_edit_commit_changes()
{
  $('#postaction').val("publish");
  $('#page_edit_form').submit();
}

function page_edit_preview()
{
  $('#postaction').val("preview");
  $('#page_edit_form').submit();
}

function page_edit_cancel()
{
  $('#postaction').val("cancel");
  $('#page_edit_form').submit();
}

function LoadRevChooser(page_id)
{
  $.getJSON(CONFIG.BASEURI+'page/revisions/'+page_id, {}, function(response){
    if(response.result == 0)
    {
      if(response.message != "ok")
      {
        AddNotice(response.message);
      }

      if(response.content)
      {
        $('#rev_chooser').empty();
        var options = jQuery.map(response.content, function(opt, i){
          return $.create('option', {'value': opt.rev_id}, [opt.rev_date+' ('+opt.username+'): '+opt.rev_page_name+((opt.page_revision == opt.rev_id) ? ' (current)' : '')]);
        });

        var sb = $.create('select',{'class': 'selectbox single', 'size': '1', 'style': 'width: auto;', 'id': 'revision_select', 'name': 'revision_select'}, options);
        sb.change(function(){ShowRevision(page_id);});
        $('#rev_chooser').append(
          $.create('form', {'method':'post', 'action': CONFIG.BASEURI+'page/setrev/'+page_id}, [
            sb,
            ' ',
            $.create('input',{'type': 'submit','class': 'button', 'value': 'set revision'},[])
          ])
        );

        $('#revision_select option[value='+response.page_revision+']').attr("selected","selected");

        $('#rev_chooser').show();
      }
      else
      {
        AddError('Server response had no content.');
      }
    }
    else
    {
      AddError(response.message);
    }
  });
}

function ShowRevision(page_id)
{
  $('#revision_select').attr("disabled","disabled");
  var rev_id = $('#revision_select option:selected').val();
  $.getJSON(CONFIG.BASEURI+'page/revisions/'+page_id, {'rid': rev_id}, function(response){
    if(response.result == 0)
    {
      if(response.message != "ok")
      {
        AddNotice(response.message);
      }

      if(response.content)
      {
        $('#rev_content').empty();

        $('#rev_content').append(
          $.create('div',{'class': 'content_box'},[
            $.create('div', {'class': 'cb_header'}, [
              $.create('span', {'class': 'text'}, [response.content.rev_page_name])
            ]),
            $.create('div', {'class': 'cb_subheader'}, [
              $.create('span', {'class': 'text'}, [response.content.rev_page_tagline])
            ]),
            $.create('div',{'class': 'cb_content'},[
              $.create('div', {'class': 'cb_content_actual', 'id': 'builder_content_actual'}, [
                response.content.rev_page_content,
                $.create('div', {'class': 'smalltext', 'style': 'float: right;'}, [
                  'Page Updated: '+response.content.page_updated,
                  '<br />',
                  'Content Updated: '+response.content.rev_date+' by '+response.content.displayname+' ('+response.content.username+')'
                ])
              ])
            ])
          ])
        );
      }
      else
      {
        AddError('Server response had no content.');
      }
    }
    else
    {
      AddError(resp.message);
    }
  });

  $('#revision_select').attr("disabled","");
}

function init()
{
  linkTargets("external","_blank");
  $('textarea.txtarea:not(.processed)').TextAreaResizer();
}

OnPageLoad(init);
