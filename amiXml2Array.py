import cookielib
import urllib
import urllib2
import xml.etree.ElementTree as elements
import string

class AmiXml2Array:
	def __init__(self,globalUrl,loginUrl):
		self.globalUrl = globalUrl
		self.loginUrl  = loginUrl 
    
	
	def login_ami_http(self,loginUrl):

		cj = cookielib.CookieJar()
		opener = urllib2.build_opener(urllib2.HTTPCookieProcessor(cj))
		urllib2.install_opener(opener)
  		con = urllib2.Request(loginUrl)
		response = urllib2.urlopen(con)
		return response.read()

	def logoff_ami_http(self,globalUrl):
		con = urllib2.Request(self.globalUrl+'action=logoff')
		response = urllib2.urlopen(con)
		return response.read()


	def get_simple_action(self,action):
		
		self.login_ami_http(self.loginUrl)
		url = self.globalUrl+'action='+action;			
		con = urllib2.Request(url)
		response = urllib2.urlopen(con)			
		self.logoff_ami_http(self.globalUrl)			
		xml_nodes = response.read()
		json = self.parse_xml(xml_nodes)
		return json

	def get_queue_status_by_name(self, queueName):
		self.login_ami_http(self.loginUrl)
		url = self.globalUrl+'action=QueueStatus&queue='+queueName
		con = urllib2.Request(url)
		response = urllib2.urlopen(con)			
		self.logoff_ami_http(self.globalUrl)			
		xml_nodes = response.read()
		json = self.parse_xml(xml_nodes)
		return json

	def get_sipstatus_by_sip_peer(self,sipPeeer):
		self.login_ami_http(self.loginUrl)
		url = self.globalUrl+'action=SIPshowpeer&peer='+str(sipPeeer)
		con = urllib2.Request(url)
		response = urllib2.urlopen(con)			
		self.logoff_ami_http(self.globalUrl)			
		xml_nodes = response.read()
		json = self.parse_xml(xml_nodes)
		return json		

	def parse_xml(self,xmlString):
		newsitems = []
		itens = elements.fromstring(xmlString)
		# root = itens.getroot()

		for item in itens.findall('./response'):
		 	for child in item:
		 		newChild = string.replace(str(child.attrib),'"','')
		 		newsitems.append(newChild)
		
		outPutFormat = string.replace(str(newsitems),'"{','{')
		outPutFormat = string.replace(outPutFormat,'}"','}')
		
		return  string.replace(outPutFormat,'\'','"')


globalUrl = 'http://192.168.1.10:8088/mxml?'
loginUrl = 'http://192.168.1.10:8088/mxml?action=login&username=asterisk&secret=asterisk'
ami = AmiXml2Array(globalUrl,loginUrl)

#Exemplo de utilizacao
print(ami.get_simple_action('QueueStatus'))
print(ami.get_queue_status_by_name('fila_teste'))
print(ami.get_sipstatus_by_sip_peer('11004'))
