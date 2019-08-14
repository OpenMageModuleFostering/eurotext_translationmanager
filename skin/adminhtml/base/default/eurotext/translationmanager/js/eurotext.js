// allow utf8-detection: öäü€

jQuery(document).ready(function()
{
	if (Translator==null)
	{
		return;
	}

	Translator.add('Do you really want to delete this project?','Möchten Sie dieses Projekt wirklich löschen?');
});

jQuery.fn.setPropAndAttr = function (attrName, attrValue) {
    return this.each(function () {
        jQuery(this).attr(attrName, attrValue);

        if (jQuery(this).prop) {
            jQuery(this).prop(attrName, attrValue);
        }
    });
};

function eurotext_closeme()
{
	window.close();
	return false;
}

function eurotext_confirmdelete()
{
	if (confirm(Translator.translate("Do you really want to delete this project?"))==true)
	{
		return true;
	}

	return false;
}

function eurotext_confirmreset()
{
	if (confirm(Translator.translate("Do you really want to reset this project?"))==true)
	{
		return true;
	}

	return false;
}

function eurotext_endsWith(str,suffix)
{
    return str.indexOf(suffix, str.length - suffix.length) !== -1;
};

function eurotext_uploadzip()
{
	var fname=jQuery("#zipfile").val();
	fname=fname.toUpperCase();

	if (eurotext_endsWith(fname,".ZIP"))
	{
		document.getElementById("zipfile_form").submit();
	}
	else
	{
		alert(Translator.translate("No file was selected, or the file selected is not a ZIP file"));
	}
	return false;
}

function eurotext_startimport()
{
	jQuery("#eurotext_importbtt").attr("disabled",true);
	jQuery("#eurotext_importprogress").html(Translator.translate("Processing - please wait …"));
	eurotext_importstep_offset=0;

	eurotext_importstep();

	return false;
}

function eurotext_importstep_retry()
{
	jQuery("#eurotext_importprogress").html(Translator.translate("Processing - please wait …"));

	eurotext_importstep();

	return false;
}

var eurotext_importstep_offset=0;
function eurotext_importstep()
{
	var postdata=new Array();
	postdata["form_key"]=eurotext_formkey;
	postdata["project_id"]=eurotext_project_id;
	postdata["offset"]=eurotext_importstep_offset;

	var postparam=eurotext_eurotext_buildpostparam(postdata);

	jQuery.ajax({
		type: "POST",
		url: eurotext_project_importstepurl,
		data: postparam,
		success: function(data)
		{
			var jsonData=null;

			try
			{
				jsonData=jQuery.parseJSON(data);
			}
			catch(e)
			{
				jQuery("#eurotext_importprogress").html("<span style='color:red;font-weight:bold;'>"+e+"<br>"+data+"</span> [ <a href='#' onclick='return eurotext_importstep_retry()'>"+Translator.translate("Try again")+"</a> ]");
			}

			if (typeof jsonData !== "undefined")
			{
				if (jsonData.status_code=="ok")
				{
					jQuery("#eurotext_importprogress").html(jsonData.status_msg);

					// Update positions:
					eurotext_importstep_offset=jsonData.offset;

					if (jsonData.finished=="0")
					{
						eurotext_importstep();
					}
					else
					{
						location.reload();
					}
				}
				else
				{
					jQuery("#eurotext_importprogress").html("<span style='color:red;font-weight:bold;'>"+jsonData.status_msg+"</span> [ <a href='#' onclick='return eurotext_importstep_retry()'>"+Translator.translate("Try again")+"</a> ]");
				}
			}
		},
		error: function(data,textStatus,errorThrown)
		{
			jQuery("#eurotext_importprogress").html("<span style='color:red;font-weight:bold;'>"+errorThrown+"</span> [ <a href='#' onclick='return eurotext_importstep_retry()'>"+Translator.translate("Try again")+"</a> ]");
		}
	});

	return false;
}


/************** eMail Templates ***********************************/
var eurotext_selectemails_ignore=false;
function eurotext_selectemail(field,file_hash)
{
	if (eurotext_selectemails_ignore)
	{
		return false;
	}

	eurotext_selectemails_ignore=true;

	var fieldName="#et_selemail"+field+"_"+file_hash;

	var checkboxActive=jQuery(fieldName).is(":checked");
	var checkboxActiveVal=checkboxActive ? "enabled" : "disabled";

	jQuery(".et_selemail_"+file_hash).attr("checked",checkboxActive);

	var postdata=new Array();
	postdata['cnt']=1;
	postdata['file_hash_0']=file_hash;
	postdata['set_0']=checkboxActiveVal;

	eurotext_selectedemails_send(postdata,function()
	{
		eurotext_selectemails_ignore=false;
	});
	return false;
}

function eurotext_selectedemails_send(postdata,doneFunc)
{
	jQuery(".et_selemail").attr("disabled",true);
	jQuery("#et_saveinfo").html(Translator.translate("Loading - please wait..."));

	postdata["form_key"]=eurotext_formkey;
	postdata['project_id']=eurotext_projectid;

	var postparam=eurotext_eurotext_buildpostparam(postdata);

	jQuery.ajax({
		type: "POST",
		url: eurotext_selectemails_saveurl,
		data: postparam,
		success: function(data)
		{
			jQuery("#selectedemails_result").html(data);
		},
		error: function(data,textStatus,errorThrown)
		{
			jQuery("#selectedemails_result").html(errorThrown);
		},
		complete: function()
		{
			jQuery(".et_selemail").attr("disabled",false);
			jQuery("#et_saveinfo").html("");
			if (doneFunc)
			{
				doneFunc();
			}
		}
	});
	return false;
}

/************** Language Files ***********************************/
var eurotext_selectlangfile_ignore=false;
function eurotext_selectlangfile(field,line_hash)
{
	if (eurotext_selectlangfile_ignore)
	{
		return false;
	}

	eurotext_selectlangfile_ignore=true;

	var fieldName="#et_sellangfile"+field+"_"+line_hash;

	var checkboxActive=jQuery(fieldName).is(":checked");
	var checkboxActiveVal=checkboxActive ? "enabled" : "disabled";

	jQuery(".et_sellangfile_"+line_hash).attr("checked",checkboxActive);

	var postdata=new Array();
	postdata['cnt']=1;
	postdata['langfile_linehash_0']=line_hash;
	postdata['set_0']=checkboxActiveVal;

	eurotext_selectedlangfiles_send(postdata,function()
	{
		eurotext_selectlangfile_ignore=false;
	});
	return false;
}

function eurotext_selectedlangfiles_send(postdata,doneFunc)
{
	jQuery(".et_sellangfile").attr("disabled",true);
	jQuery("#et_saveinfo").html(Translator.translate("Loading - please wait..."));

	postdata["form_key"]=eurotext_formkey;
	postdata['project_id']=eurotext_projectid;

	var postparam=eurotext_eurotext_buildpostparam(postdata);

	jQuery.ajax({
		type: "POST",
		url: eurotext_selectlangfiles_saveurl,
		data: postparam,
		success: function(data)
		{
			jQuery("#selectedlangfiles_result").html(data);
		},
		error: function(data,textStatus,errorThrown)
		{
			jQuery("#selectedlangfiles_result").html(errorThrown);
		},
		complete: function()
		{
			jQuery(".et_sellangfile").attr("disabled",false);
			jQuery("#et_saveinfo").html("");
			if (doneFunc)
			{
				doneFunc();
			}
		}
	});
	return false;
}

/************** CMS-Page ***********************************/
var eurotext_selectcmspage_ignore=false;
function eurotext_selectcmspage(field,cmspage_id)
{
	if (eurotext_selectcmspage_ignore)
	{
		return false;
	}

	eurotext_selectcmspage_ignore=true;

	var fieldName="#et_selcmspage"+field+"_"+cmspage_id;

	var checkboxActive=jQuery(fieldName).is(":checked");
	var checkboxActiveVal=checkboxActive ? "enabled" : "disabled";

	jQuery(".et_selcmspage_"+cmspage_id).attr("checked",checkboxActive);

	var postdata=new Array();
	postdata['cnt_pages']=1;
	postdata['page_id_0']=cmspage_id;
	postdata['setpage_0']=checkboxActiveVal;

	eurotext_selectedcmspages_send(postdata,function()
	{
		eurotext_selectcmspage_ignore=false;
	});
	return false;
}

function eurotext_selectcmsblock(field,cmsblock_id)
{
	if (eurotext_selectcmspage_ignore)
	{
		return false;
	}

	eurotext_selectcmspage_ignore=true;

	var fieldName="#et_selcmsblock"+field+"_"+cmsblock_id;

	var checkboxActive=jQuery(fieldName).is(":checked");
	var checkboxActiveVal=checkboxActive ? "enabled" : "disabled";

	jQuery(".et_selcmsblock_"+cmsblock_id).attr("checked",checkboxActive);

	var postdata=new Array();
	postdata['cnt_blocks']=1;
	postdata['block_id_0']=cmsblock_id;
	postdata['setblock_0']=checkboxActiveVal;

	eurotext_selectedcmspages_send(postdata,function()
	{
		eurotext_selectcmspage_ignore=false;
	});
	return false;
}

function eurotext_selectedcmspages_send(postdata,doneFunc)
{
	jQuery(".et_selcmspage").attr("disabled",true);
	jQuery(".et_selcmsblock").attr("disabled",true);
	jQuery("#et_saveinfo").html(Translator.translate("Loading - please wait..."));

	postdata["form_key"]=eurotext_formkey;
	postdata['project_id']=eurotext_projectid;

	var postparam=eurotext_eurotext_buildpostparam(postdata);

	jQuery.ajax({
		type: "POST",
		url: eurotext_selectcmspages_saveurl,
		data: postparam,
		success: function(data)
		{
			jQuery("#selectedcmspages_result").html(data);
		},
		error: function(data,textStatus,errorThrown)
		{
			jQuery("#selectedcmspages_result").html(errorThrown);
		},
		complete: function()
		{
			jQuery(".et_selcmspage").attr("disabled",false);
			jQuery(".et_selcmsblock").attr("disabled",false);
			jQuery("#et_saveinfo").html("");
			if (doneFunc)
			{
				doneFunc();
			}
		}
	});
	return false;
}

/************** Category ***********************************/
var eurotext_selectcategory_ignore=false;
function eurotext_selectcategory(field,category_id)
{
	if (eurotext_selectcategory_ignore)
	{
		return;
	}

	eurotext_selectcategory_ignore=true;

	var fieldName="#et_selcategory"+field+"_"+category_id;

	jQuery(".et_selcategoryitem").attr("disabled",true);

	var checkboxActive=jQuery(fieldName).is(":checked");
	var checkboxActiveVal=checkboxActive ? "enabled" : "disabled";

	jQuery(".et_selcategory_"+category_id).attr("checked",checkboxActive);

	var postdata=new Array();
	postdata['cnt']=1;
	postdata['category_id_0']=category_id;
	postdata['set_0']=checkboxActiveVal;

	eurotext_selectedcategories_send(postdata,false,function()
	{
		eurotext_selectcategory_ignore=false;
		this.location.reload();
	});
	return false;
}

function eurotext_selectedcategories_send(postdata,showResult,doneFunc)
{
	jQuery(".et_selcategory").attr("disabled",true);
	jQuery("#et_saveinfo").html(Translator.translate("Loading - please wait..."));

	postdata["form_key"]=eurotext_formkey;
	postdata['project_id']=eurotext_projectid;

	var postparam=eurotext_eurotext_buildpostparam(postdata);

	jQuery.ajax({
		type: "POST",
		url: eurotext_selectcategories_saveurl,
		data: postparam,
		success: function(data)
		{
			if (showResult)
			{
				jQuery("#selectedcategories_result").html(data);
			}
		},
		error: function(data,textStatus,errorThrown)
		{
			if (showResult)
			{
				jQuery("#selectedcategories_result").html(errorThrown);
			}
		},
		complete: function()
		{
			if (showResult)
			{
				jQuery("#et_saveinfo").html("");
			}
			if (doneFunc)
			{
				doneFunc();
			}
		}
	});
	return false;
}

/************** Product ***********************************/
var eurotext_selectproduct_ignore=false;
function eurotext_selectproduct(field,product_id)
{
	if (eurotext_selectproduct_ignore)
	{
		return;
	}

	eurotext_selectproduct_ignore=true;

	var fieldName="#et_selproduct"+field+"_"+product_id;

	jQuery(".et_selproduct_"+product_id).attr("disabled",true);
	jQuery(".et_selproductitem").attr("disabled",true);

	var checkboxActive=jQuery(fieldName).is(":checked");
	var checkboxActiveVal=checkboxActive ? "enabled" : "disabled";

	jQuery(".et_selproduct_"+product_id).attr("checked",checkboxActive);

	var postdata=new Array();
	postdata['cnt']=1;
	postdata['product_id_0']=product_id;
	postdata['set_0']=checkboxActiveVal;

	eurotext_selectedproducts_send(postdata,function()
	{
		jQuery(".et_selproduct_"+product_id).attr("disabled",false);
		jQuery(".et_selproductitem").attr("disabled",false);

		eurotext_selectproduct_ignore=false;
	});
	return false;
}

function eurotext_selectedproducts_send(postdata,doneFunc)
{
	jQuery(".et_selproduct").attr("disabled",true);
	jQuery("#et_saveinfo").html(Translator.translate("Loading - please wait..."));

	postdata["form_key"]=eurotext_formkey;
	postdata['project_id']=eurotext_projectid;
    postdata['catids']=eurotext_catids;
	postdata['filter_status']=jQuery("#filter_status").val();
	postdata['filter_stock']=jQuery("#filter_stock").val();

	var postparam=eurotext_eurotext_buildpostparam(postdata);

	jQuery.ajax({
		type: "POST",
		url: eurotext_selectproducts_saveurl,
		data: postparam,
		success: function(data)
		{
            var htmldata=data['htmldata'];
            var proddata=data['products'];
            var catdata=data['categories'];
			jQuery("#selectedproducts_result").html(htmldata);

            jQuery(".et_selproductitem").setPropAndAttr("checked",false);
            for(idx=0; idx<proddata.length; idx++)
            {
                jQuery(".et_selproduct_"+proddata[idx]).setPropAndAttr("checked",true);
            }

            jQuery(".eurotext_catsel").setPropAndAttr("checked",false);
            jQuery(".eurotext_catsel").setPropAndAttr("inderminate",false);
            for(idx=0; idx<catdata.length; idx++)
            {
                var catitem=catdata[idx];
                console.log(catitem);
                jQuery("#eurotext_catsel_"+catitem['id']).setPropAndAttr("checked",catitem['checked']);
                jQuery("#eurotext_catsel_"+catitem['id']).setPropAndAttr("indeterminate",catitem['indeterminate']);

                var catstate="unchecked";
                if (catitem['checked']) { catstate="checked"};
                if (catitem['indeterminate']) { catstate="indeterminate"};

                jQuery("#eurotext_catsel_"+catitem['id']).setPropAndAttr("x-state",catstate);
            }
		},
		error: function(data,textStatus,errorThrown)
		{
			jQuery("#selectedproducts_result").html("error: "+errorThrown);
		},
		complete: function()
		{
			jQuery("#et_saveinfo").html("");
			if (doneFunc)
			{
				doneFunc();
			}
		}
	});
	return false;
}

function eurotext_findproducts()
{
	var _pagesize=eurotext_pagesize;
	var _page=eurotext_page;
    var _catids=eurotext_catids;
	var find=jQuery("#find_products").val();

	var _filterstatus=jQuery("#filter_status").val();
	var _filterstock=jQuery("#filter_stock").val();
	var _filterproducttype=jQuery("#filter_product_type").val();

	var url=eurotext_selectproducts_url+"find/"+encodeURIComponent(find)+"/pagesize/"+_pagesize+"/page/"+_page+"/catids/"+_catids+"/status/"+_filterstatus+"/stock/"+_filterstock+"/producttype/"+encodeURIComponent(_filterproducttype);
	location.href=url;
	return false;
}

function eurotext_savesettings()
{
	jQuery("#eurotext_savebtt").html(Translator.translate("Please wait")+" …");

	var postdata=new Array();
	postdata["form_key"]=eurotext_formkey;
	postdata["username"]=jQuery("#et_username").val();
	postdata["password"]=jQuery("#et_password").val();
	postdata["customerid"]=jQuery("#et_customerid").val();

	postdata["et_products_per_file"]=jQuery("#et_products_per_file").val();
	postdata["et_categories_per_file"]=jQuery("#et_categories_per_file").val();
	postdata["et_cmspages_per_file"]=jQuery("#et_cmspages_per_file").val();

	var postparam=eurotext_eurotext_buildpostparam(postdata);

	jQuery.ajax({
		type: "POST",
		url: eurotext_saveurl,
		data: postparam,
		success: function(data)
		{
			location.reload();
		},
		error: function(data,textStatus,errorThrown)
		{
			//location.reload();
		}
	});

	return false;
}

var eurotext_select2_url="";
var eurotext_select2_project_id=-1;

function eurotext_select(url,project_id, unsetCheckbox)
{
	eurotext_select2_url=url;
	eurotext_select2_project_id=project_id;

	if (unsetCheckbox!=null)
	{
		// Checkbox mit ID unsetCheckbox deaktivieren
		jQuery("#"+unsetCheckbox).get(0).checked=false;
	}

	eurotext_saveproject(eurotext_select2);

	return false;
}

function eurotext_select2()
{
	var w=window.open(eurotext_select2_url,"","width=900, height=600, status=yes, scrollbars=yes");
	if (w==null)
	{
		alert(Translator.translate("Please deactivate your popup blocker for the Magento backend."));
	}

	return false;
}

function eurotext_sendproject()
{
	jQuery("#eurotext_angebot_btt").attr("disabled","disabled");
	jQuery("#eurotext_angebot_btt").val(Translator.translate("Please wait - this may take a while")+" …");
	jQuery("#eurotext_sendprogress").html(Translator.translate("Please wait - saving project settings")+" …");
	eurotext_saveproject(eurotext_sendproject_aftersave);
}

var eurotext_sendproject_step=0;
var eurotext_sendproject_offset=0;

function eurotext_sendproject_aftersave(id)
{
	eurotext_sendproject_step=0;
	eurotext_sendproject_offset=0;
	jQuery("#eurotext_sendprogress").html(Translator.translate("Please wait - this may take a while")+" …");
	eurotext_sendproject_singlestep();
}

function eurotext_sendproject_singlestep_retry()
{
	jQuery("#eurotext_sendprogress").html(Translator.translate("Please wait - this may take a while")+" …");
	eurotext_sendproject_singlestep();
	return false;
}

function eurotext_sendproject_singlestep()
{
	var postdata=new Array();
	postdata["form_key"]=eurotext_formkey;
	postdata["project_id"]=eurotext_project_id;
	postdata["offset"]=eurotext_sendproject_offset;
	postdata["step"]=eurotext_sendproject_step;

	var postparam=eurotext_eurotext_buildpostparam(postdata);

	jQuery.ajax({
		type: "POST",
		url: eurotext_project_sendurl,
		data: postparam,
		success: function(data)
		{
			var jsonData=null;

			try
			{
				jsonData=jQuery.parseJSON(data);
			}
			catch(e)
			{
				jQuery("#eurotext_sendprogress").html("<span style='color:red;font-weight:bold;'>"+e+"<br>"+data+"</span> &nbsp; [ <a href='#' onclick='return eurotext_sendproject_singlestep_retry()'>"+Translator.translate("Try again")+"</a> ]");
				jsonData=null;
			}

			if (jsonData!=null)
			{
				if (jsonData.status_code=="ok")
				{
					jQuery("#eurotext_sendprogress").html(jsonData.status_msg);

					// Update positions:
					eurotext_sendproject_step=jsonData.step;
					eurotext_sendproject_offset=jsonData.offset;

					if (jsonData.finished=="0")
					{
						eurotext_sendproject_singlestep();
					}
					else
					{
						location.reload();
					}
				}
				else
				{
					jQuery("#eurotext_sendprogress").html("<span style='color:red;font-weight:bold;'>"+jsonData.status_msg+"</span> &nbsp; [ <a href='#' onclick='return eurotext_sendproject_singlestep_retry()'>"+Translator.translate("Try again")+"</a> ]");
				}
			}
		},
		error: function(data,textStatus,errorThrown)
		{
			jQuery("#eurotext_sendprogress").html("<span style='color:red;font-weight:bold;'>"+errorThrown+"</span> &nbsp; [ <a href='#' onclick='return eurotext_sendproject_singlestep_retry()'>"+Translator.translate("Try again")+"</a> ]");
		}
	});

	return false;
}

function eurotext_reloadpage()
{
	location.reload();
	return false;
}

function eurotext_eurotext_buildpostparam(postdata)
{
	var postparam="";

	var i=0;
	for(var pKey in postdata)
	{
		if (postdata.hasOwnProperty(pKey))
		{
			if (i>0)
			{
				postparam+="&";
			}

			postparam+=encodeURIComponent(pKey);
			postparam+="=";
			postparam+=encodeURIComponent(postdata[pKey]);
			i++;
		}
	}

	return postparam;
}

var layer_div=null;
var layer_div2=null;

function eurotext_closelayer()
{
	if (layer_div!=null)
	{
		jQuery(layer_div).remove();
		jQuery(layer_div2).remove();

		layer_div=null;
		layer_div2=null;
	}
}

function eurotext_openlayer(content)
{
	if (layer_div!=null)
	{
		eurotext_closelayer();
	}

	layer_div=document.createElement("div");
	document.body.appendChild(layer_div);

	layer_div.style.width="100%";
	layer_div.style.height="100%";
	layer_div.style.top="0px";
	layer_div.style.left="0px";
	layer_div.style.backgroundColor="white";
	layer_div.style.opacity="0.8";
	layer_div.style.position="fixed";
	layer_div.style.display="block";
	layer_div.style.zIndex=9998;

	layer_div2=document.createElement("div");
	document.body.appendChild(layer_div2);

	layer_div2.style.top="0px";
	layer_div2.style.left="0px";
	layer_div2.style.position="fixed";
	layer_div2.style.color="black";
	layer_div2.style.fontWeight="bold";
	layer_div2.style.display="block";
	layer_div2.style.zIndex=9999;
	layer_div2.innerHTML=content;

	var x=jQuery(layer_div).width()-jQuery(layer_div2).width();
	x=x/2;

	var y=jQuery(layer_div).height()-jQuery(layer_div2).height();
	y=y/2;

	layer_div2.style.left=x+"px";
	layer_div2.style.top=y+"px";
}

function eurotext_saveproject(callback_func)
{
	eurotext_openlayer(Translator.translate("Please wait - saving project settings")+" …");

	jQuery("#eurotext_saving").html(Translator.translate("Please wait - saving project settings")+" …");
	jQuery("#btt_saveproject").attr('disabled', 'disabled');
	jQuery("#btt_saveproject").val(Translator.translate("Please wait")+" …");

	var postdata=new Array();
	postdata["form_key"]=eurotext_formkey;
	postdata["id"]=eurotext_project_id;
	postdata["project_name"]=jQuery("#form_project_name").val();
	postdata["storeview_src"]=jQuery("#form_storeview_src").val();
	postdata["storeview_dst"]=jQuery("#form_storeview_dst").val();
	postdata["langfilesmode"]=jQuery("#form_langfilesmode").is(":checked") ? "1":"0";
	postdata["export_seo"]=jQuery("#form_export_seo").is(":checked") ? "1":"0";
	postdata["export_attributes"]=jQuery("#form_export_attributes").is(":checked") ? "1":"0";
	postdata["export_urlkeys"]=jQuery("#form_export_urlkeys").is(":checked") ? "1":"0";
	postdata["productmode"]=jQuery("#form_productmode").is(":checked") ? "1":"0";
	postdata["categorymode"]=jQuery("#form_categorymode").is(":checked") ? "1":"0";
	postdata["cmsmode"]=jQuery("#form_cmsmode").is(":checked") ? "1":"0";
	postdata["templatemode"]=jQuery("#form_templatemode").is(":checked") ? "1":"0";

	var postparam=eurotext_eurotext_buildpostparam(postdata);

	jQuery.ajax({
		type: "POST",
		url: eurotext_project_saveurl,
		data: postparam,
		success: function(data)
		{
			var jsonData=null;
			try
			{
				jsonData=jQuery.parseJSON(data);
			}
			catch(e)
			{
				jQuery("#eurotext_saving").html("<span style='color:red;font-weight:bold;'>"+e+"</span>");
				jsonData=null;
			}

			if (jsonData !== null)
			{
				if (jsonData.status_code=="ok")
				{
					jQuery("#eurotext_saving").html(jsonData.status_msg);

					if (callback_func)
					{
						callback_func();
					}
				}
				else
				{
                    jQuery("#eurotext_saving").html("<span style='color:red;font-weight:bold;'>"+jsonData.status_msg+"</span>");
				}
			}
			else
			{
				jQuery("#eurotext_saving").html("<span style='color:red;font-weight:bold;'>"+data+"</span>");
			}
		},
		error: function(data,textStatus,errorThrown)
		{
			jQuery("#eurotext_saving").html("<span style='color:red;font-weight:bold;'>"+Translator.translate("Unable to save project settings")+": "+errorThrown+"</span>");
		},
		complete: function()
		{
			jQuery("#btt_saveproject").removeAttr('disabled');
			jQuery("#btt_saveproject").val(Translator.translate("Save project"));
			eurotext_closelayer();
		}
	});

	return false;
}

function eurotext_unlock2()
{
	return eurotext_unlock();
}

function eurotext_unlock()
{
	jQuery("#et_form").find("input, select").each(function()
	{
		jQuery(this).attr("disabled",false);
	});

	jQuery("#register_change").hide();
	jQuery("#register_submit").attr("disabled",false);

	return false;
}