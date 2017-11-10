/**
 *  HousePanel
 *
 *  Copyright 2016 Kenneth Washington
 *
 *  Licensed under the Apache License, Version 2.0 (the "License"); you may not use this file except
 *  in compliance with the License. You may obtain a copy of the License at:
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software distributed under the License is distributed
 *  on an "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the License
 *  for the specific language governing permissions and limitations under the License.
 *
 * This app started life displaying the history of various ssmartthings
 * but it has morphed into a full blown smart panel web application
 * it displays and enables interaction with switches, dimmers, locks, etc
 * 
 */
public static String version() { return "v1.0.alpha.rev.1" }
public static String handle() { return "HousePanel" }
definition(
    name: "${handle()}",
    namespace: "kewashi",
    author: "Kenneth Washington",
    description: "Tap here to install ${handle()} ${version()} - a highly customizable tablet smart app. ",
    category: "Convenience",
    iconUrl: "https://s3.amazonaws.com/smartapp-icons/Meta/intruder_motion-presence.png",
    iconX2Url: "https://s3.amazonaws.com/smartapp-icons/Meta/intruder_motion-presence@2x.png",
    iconX3Url: "https://s3.amazonaws.com/smartapp-icons/Meta/intruder_motion-presence@2x.png",
    oauth: [displayName: "kewashi house panel", displayLink: ""])


preferences {
    section("Lights and Switches...") {
        input "myswitches", "capability.switch", multiple: true, required: false, title: "Switches"
        input "mydimmers", "capability.switchLevel", hideWhenEmpty: true, multiple: true, required: false, title: "Dimmers"
        input "mymomentaries", "capability.momentary", hideWhenEmpty: true, multiple: true, required: false, title: "Momentary Buttons"
        input "mylights", "capability.light", hideWhenEmpty: true, multiple: true, required: false, title: "Lights"
        input "mybulbs", "capability.bulb", hideWhenEmpty: true, multiple: true, required: false, title: "Bulbs"
    }
    section ("Motion and Presence") {
    	input "mysensors", "capability.motionSensor", multiple: true, required: false, title: "Motion"
    	input "mypresences", "capability.presenceSensor", hideWhenEmpty: true, multiple: true, required: false, title: "Presence"
    }
    section ("Door and Contact Sensors") {
    	input "mycontacts", "capability.contactSensor", hideWhenEmpty: true, multiple: true, required: false, title: "Contact Sensors"
    	input "mydoors", "capability.doorControl", hideWhenEmpty: true, multiple: true, required: false, title: "Doors"
    	input "mylocks", "capability.lock", hideWhenEmpty: true, multiple: true, required: false, title: "Locks"
    }
    section ("Music players") {
    	input "mymusics", "capability.musicPlayer", hideWhenEmpty: true, multiple: true, required: false, title: "Music Players"
    }
    section ("Thermostat & Environment") {
    	input "mythermostats", "capability.thermostat", hideWhenEmpty: true, multiple: true, required: false, title: "Thermostats"
    	input "mytemperatures", "capability.temperatureMeasurement", hideWhenEmpty: true, multiple: true, required: false, title: "Temperature Measures"
    	input "myilluminances", "capability.illuminanceMeasurement", hideWhenEmpty: true, multiple: true, required: false, title: "Illuminances"
    	input "myweathers", "device.smartweatherStationTile", hideWhenEmpty: true, title: "Weather tile", multiple: false, required: false
    }
    section ("Water") {
    	input "mywaters", "capability.waterSensor", hideWhenEmpty: true, multiple: true, required: false, title: "Water Sensors"
    	input "mysmokes", "capability.smokeDetector", hideWhenEmpty: true, multiple: true, required: false, title: "Smoke Detectors"
    	input "myvalves", "capability.valve", hideWhenEmpty: true, multiple: true, required: false, title: "Sprinklers"
    }
    section ("Other Sensors (duplicates ignored)...") {
    	input "myothers", "capability.sensor", multiple: true, required: false
    }
}

mappings {
  path("/switches") {
    action: [
      POST: "getSwitches"
    ]
  }
  
  path("/bulbs") {
    action: [
      POST: "getBulbs"
    ]
  }
  
  path("/lights") {
    action: [
      POST: "getLights"
    ]
  }
  
  path("/dimmers") {
    action: [
      POST: "getDimmers"
    ]
  }

  path("/sensors") {
    action: [
      POST: "getSensors"
    ]
  }
    
  path("/contacts") {
    action: [
      POST: "getContacts"
    ]
  }

  path("/momentaries") {
    action: [
      POST: "getMomentaries"
    ]
  }
    
  path("/locks") {
    action: [
      POST: "getLocks"
    ]
  }
    
  path("/musics") {
    action: [
        POST: "getMusics"
    ]
  }
    
  path("/thermostats") {
    action: [
      POST: "getThermostats"
    ]
  }
    
  path("/presences") {
    action: [
      POST: "getPresences"
    ]
  }
  
  path("/valves") {
    action: [
      POST: "getValves"
    ]
  }
    
  path("/waters") {
    action: [
      POST: "getWaters"
    ]
  }
    
  path("/others") {
    action: [
      POST: "getOthers"
    ]
  }
    
  path("/weathers") {
    action: [
      POST: "getWeathers"
    ]
  }
   
  path("/doors") {
    action: [ POST: "getDoors" ]
  }
  path("/illuminances") {
    action: [ POST: "getIlluminances" ]
  }
  path("/smokes") {
    action: [ POST: "getSmokes" ]
  }
  path("/temperatures") {
    action: [ POST: "getTemperatures" ]
  }
  path("/modes") {
    action: [
      POST: "getModes"
    ]
  }
    
  path("/pistons") {
    action: [
      POST: "getPistons"
    ]
  }
  
  path("/doaction") {
     action: [
       POST: "doAction"
     ]
  }
  
  path("/doquery") {
     action: [
       POST: "doQuery"
     ]
  }

  path("/gethistory") {
     action: [
       POST: "getHistory"
    ]
  }

}

def installed() {
	log.debug "Installed with settings: ${settings}"
    // activate connector for webCoRE
    webCoRE_init()
}

def updated() {
	log.debug "Updated with settings: ${settings}"
    webCoRE_init()
}

def switchHandler(evt) {
	def item = evt.getDevice()
    def evalue = evt.value
    def swid = item.id
    def swname = item.displayName
    log.debug "Event received from device = ${swname} value = ${evalue} id = ${swid}"
}

def getWeatherInfo(evt) {
	def name = evt.getName()
    def src = evt.getSource()
    def val = evt.getValue()
	log.debug "Weather event: from ${src} name = ${name} value = ${val}"
}

def getSwitch(swid, item=null) {
    getThing(myswitches, "switch", swid, item)
    // item = item ? item : myswitches.find {it.id == swid }
    // def resp = item ? [switch: item.currentValue("switch")] : false
    // return resp
}

def getBulb(swid, item=null) {
    item = item ? item : mybulbs.find {it.id == swid }
    def resp = item ? [bulb: item.currentValue("bulb")] : false
    return resp
}

def getLight(swid, item=null) {
    item = item ? item : mylights.find {it.id == swid }
    def resp = item ? [light: item.currentValue("light")] : false
    return resp
}

def getMomentary(swid, item=null) {
	def resp = false
    item = item ? item : mymomentaries.find {it.id == swid }
    if ( item && item.hasCapability("Switch") ) {
	    def curval = item.currentValue("switch")
    	if (curval!="on" && curval!="off") { curval = "off" }
    	resp = [momentary: item.currentValue("switch")]
    }
    return resp
}

def getDimmer(swid, item=null) {
    item = item ? item : mydimmers.find {it.id == swid }
    def resp = item ? [switch: item.currentValue("switch"),
                       level: item.currentValue("level")] : false
    return resp
}

def getSensor(swid, item=null) {
    item = item ? item : mysensors.find {it.id == swid }
    def resp = item ? [motion: item.currentValue("motion")] : false
    return resp
}

def getContact(swid, item=null) {
    item = item ? item : mycontacts.find {it.id == swid }
    def resp = item ? [contact: item.currentValue("contact")] : false
    return resp
}

def getLock(swid, item=null) {
    item = item ? item : mylocks.find {it.id == swid }
    def resp = item ? [lock: item.currentValue("lock")] : false
    return resp
}

def getMusic(swid, item=null) {
    item = item? item : mymusics.find {it.id == swid }
    def resp = item ?   [track: item.currentValue("trackDescription"),
                              musicstatus: item.currentValue("status"),
                              level: item.currentValue("level"),
                              musicmute: item.currentValue("mute")
                        ] : false
    return resp
}

def getThermostat(swid, item=null) {
    item = item? item : mythermostats.find {it.id == swid }
    def resp = item ?   [temperature: item.currentValue("temperature"),
                              heat: item.currentValue("heatingSetpoint"),
                              cool: item.currentValue("coolingSetpoint"),
                              thermofan: item.currentValue("thermostatFanMode"),
                              thermomode: item.currentValue("thermostatMode"),
                              thermostate: item.currentValue("thermostatOperatingState")
                         ] : false
    // log.debug "Thermostat response = ${resp}"
    return resp
}

// use absent instead of "not present" for absence state
def getPresence(swid, item=null) {
    item = item ? item : mypresences.find {it.id == swid }
    def resp = item ? [presence : (item.currentValue("presence")=="present") ? "present" : "absent"] : false
    return resp
}

def getWater(swid, item=null) {
    item = item ? item : mywaters.find {it.id == swid }
    def resp = item ? [water: item.currentValue("water")] : false
    return resp
}

def getValve(swid, item=null) {
    item = item ? item : myvalves.find {it.id == swid }
    def resp = item ? [valve: item.currentValue("valve")] : false
    return resp
}
def getDoor(swid, item=null) {
    item = item ? item : mydoors.find {it.id == swid }
    def resp = item ? [door: item.currentValue("door")] : false
    return resp
}
def getIlluminance(swid, item=null) {
    item = item ? item : myilluminances.find {it.id == swid }
    def resp = item ? [illuminance: item.currentValue("illuminance")] : false
    return resp
}
def getSmoke(swid, item=null) {
    item = item ? item : mysmokes.find {it.id == swid }
    def resp = item ? [smoke: item.currentValue("smoke"),
                       carbonMonoxide: item.currentValue("carbonMonoxide")] : false
    return resp
}
def getTemperature(swid, item=null) {
    getThing(mytemperatures, "temperature", swid, item)
}

def getWeather(swid, item=null) {
    item = item ? item : myweathers.find {it.id == swid }
    def resp = false
    if (item) {
		resp = [:]
		def attrs = item.getSupportedAttributes()
		attrs.each {att ->
        	def attname = att.name
        	def attval = item.currentValue(attname)
            resp.put(attname,attval)
    	}
    }
    return resp
}

def getOther(swid, item=null) {
    item = item ? item : myothers.find {it.id == swid }
    def resp = false
    
            item?.capabilities.each {cap ->
                def capname = cap.getName()
                resp = [:]
                cap.attributes?.each {attr ->
                    def othername = attr.getName()
                    def othervalue = item.currentValue(othername)
                    if ( othervalue ) { 
                    	resp.put(othername,othervalue)
                    }
                }
            }
    return resp
}

def getMode(swid=0, item=null) {
    def resp = [:]
    resp =  [   name: location.getName(),
                zipcode: location.getZipCode(),
                themode: location.getMode()
            ];
    return resp
}

// this returns just a single active mode, not the list of available modes
// this is done so we can treat this like any other set of tiles
def getModes() {
    def resp = []
    // log.debug "Getting the mode tile"
    def val = getMode()
    resp << [name: "Mode 1x1", id: "mode1x1", value: val, type: "mode"]
    resp << [name: "Mode 1x2", id: "mode1x2", value: val, type: "mode"]
    resp << [name: "Mode 2x1", id: "mode2x1", value: val, type: "mode"]
    resp << [name: "Mode 2x2", id: "mode2x2", value: val, type: "mode"]
    return resp
}

def getPiston(swid, item=null) {
    item = item ? item : webCoRE_list().find {it.id == swid}
    def resp = [webcore: "webCoRE piston", pistonName: item.name]
    return resp
}

// make a generic thing getter to streamline the code
def getThing(things, thingtype, swid, item=null) {
    item = item ? item : things.find {it.id == swid }
    def resp = item ? ["$thingtype": item.currentValue(thingtype)] : false
    return resp
}

// make a generic thing list getter to streamline the code
def getThings(things, thingtype) {
    def resp = []
    def n  = things ? things.size() : 0
    log.debug "Number of things of type ${thingtype} = ${n}"
    things?.each {
        // def val = thingfunc(it.id, it)
        def val = ["$thingtype": it.currentValue(thingtype)]
        resp << [name: it.displayName, id: it.id, value: val, type: thingtype]
    }
    return resp
}

def getPistons() {
    def resp = []
    def plist = webCoRE_list()
    log.debug "Number of pistons = " + plist?.size() ?: 0
    plist?.each {
        def val = getPiston(it.id, it)
        resp << [name: it.name, id: it.id, value: val, type: "piston"]
        // log.debug "webCoRE piston retrieved: name = ${it.name} with id = ${it.id} and ${it}"
    }
    return resp
}

def getSwitches() {
    getThings(myswitches, "switch")
}

def getBulbs() {
    def resp = []
    def n  = mybulbs ? mybulbs.size() : 0
    log.debug "Number of bulbs = ${n}"
    mybulbs?.each {
        def val = getBulb(it.id, it)
        resp << [name: it.displayName, id: it.id, value: val, type: "bulb"]
    }
    return resp
}

def getLights() {
    def resp = []
    def n  = mylights ? mylights.size() : 0
    log.debug "Number of lights = ${n}"
    mybulbs?.each {
        def val = getLight(it.id, it)
        resp << [name: it.displayName, id: it.id, value: val, type: "light"]
    }
    return resp
}

def getDimmers() {
    log.debug "Number of dimmers = " + mydimmers?.size() ?: 0
    def resp = []
    mydimmers?.each {
        def multivalue = getDimmer(it.id, it)
        resp << [name: it.displayName, id: it.id, value: multivalue, type: "switchlevel"]
    }
    return resp
}

def getSensors() {
    def resp = []
    log.debug "Number of motion sensors = " + mysensors?.size() ?: 0
    mysensors?.each {
        def val = getSensor(it.id, it)
        resp << [name: it.displayName, id: it.id, value: val, type: "motion"]
    }
    return resp
}

def getContacts() {
    def resp = []
    log.debug "Number of contact sensors = " + mycontacts?.size() ?: 0
    mycontacts?.each {
        def val = getContact(it.id, it)
        resp << [name: it.displayName, id: it.id, value: val, type: "contact"]
    }
    return resp
}

def getMomentaries() {
    def resp = []
    log.debug "Number of momentaries = " + mymomentaries?.size() ?: 0
    mymomentaries?.each {
        if ( it.hasCapability("Switch") ) {
            def val = getMomentary(it.id, it)
            resp << [name: it.displayName, id: it.id, value: val, type: "momentary" ]
        }
    }
    return resp
}

def getLocks() {
    def resp = []
    log.debug "Number of locks = " + mylocks?.size() ?: 0
    mylocks?.each {
        def val = getLock(it.id, it)
        resp << [name: it.displayName, id: it.id, value: val, type: "lock" ]
    }
    return resp
}

def getMusics() {
    def resp = []
    log.debug "Number of music players = " + mymusics?.size() ?: 0
    mymusics?.each {
        def multivalue = getMusic(it.id, it)
        resp << [name: it.displayName, id: it.id, value: multivalue, type: "music"]
    }
    return resp
}

def getThermostats() {
    def resp = []
    log.debug "Number of thermostats = " + mythermostats?.size() ?: 0
    mythermostats?.each {
        def multivalue = getThermostat(it.id, it)
        resp << [name: it.displayName, id: it.id, value: multivalue, type: "thermostat" ]
    }
    return resp
}

def getPresences() {
    getThings(mypresences, "presence")
}
def getWaters() {
    getThings(mywaters, "water")
}
def getValves() {
    getThings(myvalves, "valve")
}
def getDoors() {
    getThings(mydoors, "door")
}
def getIlluminances() {
    getThings(myilluminances, "illuminance")
}
def getSmokes() {
    getThings(mysmokes, "smoke")
}
def getTemperatures() {
    getThings(mytemperatures, "temperature")
}

def getWeathers() {
	// def resp = getGenerals(myweathers, "weather")
    def resp = []
    
    myweathers?.each {
	    def multivalue = [:]
    	def that = it
    	def attrs = it.getSupportedAttributes()
		attrs.each {att ->
        	def attname = att.name
        	def attval = that.currentValue(attname)
            multivalue.put(attname,attval)
            // log.debug "Supported ${that.displayName} Attribute: ${attname} value = ${attval}"
        }
        resp << [name: that.displayName, id: that.id, value: multivalue, type: "weather"]
    }
    
    return resp
}

def getOthers() {
	return getGenerals(myothers, "other")
}

def getGenerals(mygens, gentype) {
    def resp = []
    def uniquenum = 0
    log.debug "Number of ${gentype} sensors = ${mygens ? mygens.size() : 0}"
    mygens?.each {
        
        def thatid = it.id;
        def inlist = ( myswitches?.find {it.id == thatid } ||
             mydimmers?.find {it.id == thatid } ||
             mycontacts?.find {it.id == thatid } ||
             mylocks?.find {it.id == thatid } ||
             mysensors?.find {it.id == thatid} ||
             mymusics?.find {it.id == thatid } ||
             mythermostats?.find {it.id == thatid} ||
             myweathers?.find {it.id == thatid} ||
             mypresences?.find {it.id == thatid}
            )
        
        if ( !inlist ) {
            uniquenum++

            // log each capability supported with all its supported attributes
            def that = it
            def multivalue = [:]
            it.capabilities.each {cap ->
                def capname = cap.getName()
                // log.debug "Capability name: ${capname}"
                cap.attributes?.each {attr ->
                    def othername = attr.getName()
                    def othervalue = that.currentValue(othername)
                    if ( othervalue ) { 
                    	multivalue.put(othername,othervalue)
                    	// log.debug "-- Attribute Name= ${othername} Value= ${othervalue}"
                    }
                }
            }
            resp << [name: it.displayName, id: it.id, value: multivalue, type: gentype]
            // log.debug it.displayName + " = " + multivalue
        }
        log.debug "Number of unique ${gentype} sensors = " + uniquenum
    }
    return resp
}

def autoType(swid) {
	def swtype
    if ( mydimmers?.find {it.id == swid } ) { swtype= "switchlevel" }
    else if ( mymomentaries?.find {it.id == swid } ) { swtype= "momentary" }
    else if ( mylights?.find {it.id == swid } ) { swtype= "light" }
    else if ( mybulbs?.find {it.id == swid } ) { swtype= "bulb" }
    else if ( myswitches?.find {it.id == swid } ) { swtype= "switch" }
    else if ( mylocks?.find {it.id == swid } ) { swtype= "lock" }
    else if ( mymusics?.find {it.id == swid } ) { swtype= "music" }
    else if ( mythermostats?.find {it.id == swid} ) { swtype = "thermostat" }
    else if ( mypresences?.find {it.id == swid } ) { swtype= "presence" }
    else if ( myweathers?.find {it.id == swid } ) { swtype= "weather" }
    else if ( mysensors?.find {it.id == swid } ) { swtype= "motion" }
    else if ( mydoors?.find {it.id == swid } ) { swtype= "door" }
    else if ( mycontacts?.find {it.id == swid } ) { swtype= "contact" }
    else if ( mywaters?.find {it.id == swid } ) { swtype= "water" }
    else if ( myilluminances?.find {it.id == swid } ) { swtype= "illuminance" }
    else if ( mysmokes?.find {it.id == swid } ) { swtype= "smoke" }
    else if ( mytemperatures?.find {it.id == swid } ) { swtype= "temperature" }
    else if ( myothers?.find {it.id == swid } ) { swtype= "other" }
    else { swtype = "mode" }
    return swtype
}

def doAction() {
    // returns false if the item is not found
    // otherwise returns a JSON object with the name, value, id, type
    def cmd = params.swvalue
    def swid = params.swid
    def swtype = params.swtype
    def swattr = params.swattr
    def cmdresult = false
    // sendLocationEvent( [name: "housepanel", value: "touch", isStateChange:true, displayed:true, data: [id: swid, type: swtype, attr: swattr, cmd: cmd] ] )

	// get the type if auto is set
    if (swtype=="auto" || swtype=="none" || swtype=="") {
        swtype = autoType(swid)
    }

    switch (swtype) {
      case "switch" :
      	 cmdresult = setSwitch(swid, cmd, swattr)
         break
         
      case "bulb" :
      	 cmdresult = setBulb(swid, cmd, swattr)
         break
         
      case "light" :
      	 cmdresult = setLight(swid, cmd, swattr)
         break
         
      case "switchlevel" :
         cmdresult = setDimmer(swid, cmd, swattr)
         break
         
      case "momentary" :
         cmdresult = setMomentary(swid, cmd, swattr)
         break
      
      case "lock" :
         cmdresult = setLock(swid, cmd, swattr)
         break
         
      case "thermostat" :
         cmdresult = setThermostat(swid, cmd, swattr)
         break
         
      case "music" :
         cmdresult = setMusic(swid, cmd, swattr)
         break
        
      // note: this requires a special handler for motion to manually set it
      case "motion" :
        // log.debug "Manually setting motion sensor with id = $swid"
    	cmdresult = setSensor(swid, cmd, swattr)
        break

      case "mode" :
         cmdresult = setMode(swid, cmd, swattr)
         break
         
      case "valve" :
      	 cmdresult = setValve(swid, cmd, swattr)
         break

      case "door" :
      	 cmdresult = setDoor(swid, cmd, swattr)
         break

      case "piston" :
         webCoRE_execute(swid)
         // set the result to piston information (could be false)
         cmdresult = getPiston(swid)
         // log.debug "Executed webCoRE piston: $cmdresult"
         break;
      
    }
   
    // log.debug "cmd = $cmd type = $swtype id = $swid cmdresult = $cmdresult"
    return cmdresult

}

// get a tile by the ID not object
def doQuery() {
    def swid = params.swid
    def swtype = params.swtype
    def cmdresult = false

	// get the type if auto is set
    if (swtype=="auto" || swtype=="none" || swtype=="") {
        swtype = autoType(swid)
    }

    switch(swtype) {
    case "switch" :
      	cmdresult = getSwitch(swid)
        break
         
    case "bulb" :
      	cmdresult = getBulb(swid)
        break
         
    case "light" :
      	cmdresult = getLight(swid)
        break
         
    case "switchlevel" :
        cmdresult = getDimmer(swid)
        break
         
    case "momentary" :
        cmdresult = getMomentary(swid)
        break
        
    case "motion" :
    	cmdresult = getSensor(swid)
        break
        
    case "contact" :
    	cmdresult = getContact(swid)
        break
      
    case "lock" :
        cmdresult = getLock(swid)
        break
         
    case "thermostat" :
        cmdresult = getThermostat(swid)
        break
         
    case "music" :
        cmdresult = getMusic(swid)
        break
        
    case "presence" :
    	cmdresult = getPresence(swid)
        break
         
    case "water" :
        cmdresult = getWater(swid)
        break
         
    case "valve" :
      	cmdresult = getValve(swid)
        break
    case "door" :
      	cmdresult = getDoor(swid)
        break
    case "illuminance" :
      	cmdresult = getIlluminance(swid)
        break
    case "smoke" :
      	cmdresult = getSmoke(swid)
        break
    case "temperature" :
      	cmdresult = getTemperature(swid)
        break
    case "weather" :
    	cmdresult = getWeather(swid)
        break
        
    case "other" :
    	cmdresult = getOther(swid)
        break

    case "mode" :
        cmdresult = getMode(swid)
        break

    }
   
    // log.debug "getTile: type = $swtype id = $swid cmdresult = $cmdresult"
    return cmdresult
}

// changed these to just return values of entire tile
def setOnOff(items, itemtype, swid, cmd, swattr) {
    def newonoff = false
    def item  = items.find {it.id == swid }
    if (item) {
        if (cmd=="on" || cmd=="off") {
            newonoff = cmd
        } else {
            newonoff = item.currentValue(itemtype)=="off" ? "on" : "off"
        }
        newonoff=="on" ? item.on() : item.off()
    }
    return newonoff
    
}

def setSwitch(swid, cmd, swattr) {
    def onoff = setOnOff(myswitches, "switch", swid,cmd,swattr)
    def resp = onoff ? [switch: onoff] : false
    return resp
}

def setDoor(swid, cmd, swattr) {
    def newonoff = false
    def item  = mydoors.find {it.id == swid }
    if (item) {
        if (cmd=="open" || cmd=="close") {
            newonoff = cmd
        } else {
            newonoff = (item.currentValue("door")=="closed" ||
                        item.currentValue("door")=="closing" )  ? "opening" : "closing"
        }
        newonoff=="opening" ? item.open() : item.close()
    }
    return newonoff
}

// special function to set motion status
def setSensor(swid, cmd, swattr) {
    def resp = false
    def newsw
    def item  = mysensors.find {it.id == swid }
    // anything but active will set the motion to inactive
    if (item && item.hasCommand("startmotion") && item.hasCommand("stopmotion") ) {
        if (cmd=="active" || cmd=="move") {
            item.startmotion()
            newsw = "active"
        } else {
            item.stopmotion()
            newsw = "inactive"
        }
        resp = [motion: newsw]
    }
    return resp
    
}

// changed these to just return values of entire tile
def setBulb(swid, cmd, swattr) {
    def onoff = setOnOff(mybulbs, "bulb", swid,cmd,swattr)
    def resp = onoff ? [bulb: onoff] : false
    return resp
}

// changed these to just return values of entire tile
def setLight(swid, cmd, swattr) {
    def onoff = setOnOff(mylights, "light", swid,cmd,swattr)
    def resp = onoff ? [light: onoff] : false
    return resp
}

def setMode(swid, cmd, swattr) {
    def resp
    def themode = swattr.substring(swattr.lastIndexOf(" ")+1)
    def newsw = themode
    def allmodes = location.getModes()
    def idx=allmodes.findIndexOf{it == themode}

    if (idx!=null) {
        idx = idx+1
        if (idx == allmodes.size() ) { idx = 0 }
        newsw = allmodes[idx]
    } else {
        newsw = allmodes[0]
    }
    
    log.debug "Mode changed from $themode to $newsw index = $idx "
    location.setMode(newsw);
    resp =  [   name: location.getName(),
                zipcode: location.getZipCode(),
                themode: newsw
            ];
    
    return resp
}

def setDimmer(swid, cmd, swattr) {
    def resp = false

    def item  = mydimmers.find {it.id == swid }
    if (item) {
    
        def newonoff = item.currentValue("switch")
        def newsw = item.currentValue("level")   
         
        // log.debug "switchlevel cmd = $cmd swattr = $swattr"
        switch(swattr) {
         
        case "level-up":
            newsw = newsw.toInteger()
            newsw = (newsw >= 95) ? 100 : newsw - (newsw % 5) + 5
            item.setLevel(newsw)
            newonoff = "on"
            break
              
        case "level-dn":
            newsw = newsw.toInteger()
            def del = (newsw % 5) == 0 ? 5 : newsw % 5
            newsw = (newsw <= 5) ? 5 : newsw - del
            item.setLevel(newsw)
            newonoff = "on"
            break
              
        case "level-val":
            newonoff = newonoff=="off" ? "on" : "off"
            newonoff=="on" ? item.on() : item.off()
            break
              
        case "switchlevel switch on":
            newonoff = "off"
            item.off()
            break
              
        case "switchlevel switch off":
            newonoff = "on"
            item.on()
            break
              
        case "switch":
            if (cmd=="on" || cmd=="off") {
                newonoff = cmd
            } else {
                newonoff = newonoff=="off" ? "on" : "off"
            }
            newonoff=="on" ? item.on() : item.off()
            break
              
        default:
            if (cmd=="on" || cmd=="off") {
                newonoff = cmd
            } else {
                newonoff = newonoff=="off" ? "on" : "off"
            }
            newonoff=="on" ? item.on() : item.off()
            if ( swattr.isNumber() ) {
                newsw = swattr.toInteger()
                item.setLevel(newsw)
            }
            break               
              
        }
        
        resp = [switch: newonoff, level: newsw]   
        // resp = [name: item.displayName, value: newsw, id: swid, type: swtype]
    }

    return resp
    
}

def setMomentary(swid, cmd, swattr) {
    def resp = false

    def item  = mymomentaries.find {it.id == swid }
    if (item) {
          // log.debug "setMomentary command = $cmd for id = $swid"
        def newsw = item.currentSwitch
        item.push()
        resp = getMomentary(swid, item)
        // resp = [name: item.displayName, value: item.currentSwitch, id: swid, type: swtype]
    }
    return resp

}

def setLock(swid, cmd, swattr) {
    def resp = false
    def newsw
    def item  = mylocks.find {it.id == swid }
    if (item) {
        if (cmd!="lock" && cmd!="unlock") {
            cmd = item.currentLock=="locked" ? "unlock" : "lock"
        }
        if (cmd=="unlock") {
            item.unlock()
            newsw = "unlocked"
        } else {
            item.lock()
            newsw = "locked"
        }
        resp = [lock: newsw]
    }
    return resp

}

def setValve(swid, cmd, swattr) {
    def resp = false
    def newsw
    def item  = myvalves.find {it.id == swid }
    if (item) {
        if (cmd!="open" && cmd!="close") {
            cmd = item.currentValue=="closed" ? "open" : "close"
        }
        if (cmd=="open") {
            item.open()
            newsw = "open"
        } else {
            item.close()
            newsw = "closed"
        }
        resp = [valve: newsw]
    }
    return resp
}

def setThermostat(swid, curtemp, swattr) {
    def resp = false
    def newsw = 72
    def tempint

    def item  = mythermostats.find {it.id == swid }
//    mythermostats?.each {
    if (item) {
          log.debug "setThermostat attr = $swattr for id = $swid curtemp = $curtemp"
          resp = getThermostat(swid, item)
          switch (swattr) {
          case "heat-up":
              newsw = curtemp.toInteger() + 1
              if (newsw > 85) newsw = 85
              // item.heat()
              item.setHeatingSetpoint(newsw.toString())
              resp['heat'] = newsw
              break
          
          case "cool-up":
              newsw = curtemp.toInteger() + 1
              if (newsw > 85) newsw = 85
              // item.cool()
              item.setCoolingSetpoint(newsw.toString())
              resp['cool'] = newsw
              break

          case "heat-dn":
              newsw = curtemp.toInteger() - 1
              if (newsw < 60) newsw = 60
              // item.heat()
              item.setHeatingSetpoint(newsw.toString())
              resp['heat'] = newsw
              break
          
          case "cool-dn":
              newsw = curtemp.toInteger() - 1
              if (newsw < 65) newsw = 60
              // item.cool()
              item.setCoolingSetpoint(newsw.toString())
              resp['cool'] = newsw
              break
          
          case "thermostat thermomode heat":
              item.cool()
              newsw = "cool"
              resp['thermomode'] = newsw
              break
          
          case "thermostat thermomode cool":
              item.auto()
              newsw = "auto"
              resp['thermomode'] = newsw
              break
          
          case "thermostat thermomode auto":
              item.off()
              newsw = "off"
              resp['thermomode'] = newsw
              break
          
          case "thermostat thermomode off":
              item.heat()
              newsw = "heat"
              resp['thermomode'] = newsw
              break
          
          case "thermostat thermofan fanOn":
              item.fanAuto()
              newsw = "fanAuto"
              resp['thermofan'] = newsw
              break
          
          case "thermostat thermofan fanAuto":
              item.fanOn()
              newsw = "fanOn"
              resp['thermofan'] = newsw
              break
           
          // define actions for python end points  
          default:
              if ( item.hasCommand(cmd) ) {
                  item.${cmd}()
              }
              if (cmd=="heat" && swattr.isNumber()) {
                  item.setHeatingSetpoint(swattr)
              }
              if (cmd=="cool" && swattr.isNumber()) {
                  item.setCoolingSetpoint(swattr)
              }
            break
          }
        // resp = [name: item.displayName, value: newsw, id: swid, type: swtype]
      
    }
    return resp
}

def setMusic(swid, cmd, swattr) {
    def resp = false
    def item  = mymusics.find {it.id == swid }
    def newsw
    if (item) {
        log.debug "music command = $cmd for id = $swid swattr = $swattr"
        resp = getMusic(swid, item)
        switch(swattr) {
         
        case "level-up":
              newsw = cmd.toInteger()
              newsw = (newsw >= 95) ? 100 : newsw - (newsw % 5) + 5
              item.setLevel(newsw)
              resp['level'] = newsw
              break
              
        case "level-dn":
              newsw = cmd.toInteger()
              def del = (newsw % 5) == 0 ? 5 : newsw % 5
              newsw = (newsw <= 5) ? 5 : newsw - del
              item.setLevel(newsw)
              resp['level'] = newsw
              break

        case "music musicstatus paused":
        case "music musicstatus stopped":
              newsw = "playing"
              item.play()
              resp['musicstatus'] = newsw
              break

        case "music musicstatus playing":
              newsw = "paused"
              item.pause()
              resp['musicstatus'] = newsw
              break
              
        case "music-play":
              newsw = "playing"
              item.play()
              resp['musicstatus'] = newsw
              break
              
        case "music-stop":
              newsw = "stopped"
              item.stop()
              resp['musicstatus'] = newsw
              break
              
        case "music-pause":
              newsw = "paused"
              item.pause()
              resp['musicstatus'] = newsw
              break
              
        case "music-previous":
              item.previousTrack()
              resp['track'] = item.currentValue("trackDescription")
              break
              
        case "music-next":
              item.nextTrack()
              resp['track'] = item.currentValue("trackDescription")
              break
              
        case "music musicmute muted":
              newsw = "unmuted"
              item.unmute()
              resp['musicmute'] = newsw
              break
              
        case "music musicmute unmuted":
              newsw = "muted"
              item.mute()
              resp['musicmute'] = newsw
              break
              
         }
         // resp = [name: item.displayName, value: newsw, id: swid, type: swtype]
    }
    return resp
}

/*************************************************************************/
/* webCoRE Connector v0.2                                                */
/*************************************************************************/
/*  Copyright 2016 Adrian Caramaliu <ady624(at)gmail.com>                */
/*                                                                       */
/*  This program is free software: you can redistribute it and/or modify */
/*  it under the terms of the GNU General Public License as published by */
/*  the Free Software Foundation, either version 3 of the License, or    */
/*  (at your option) any later version.                                  */
/*                                                                       */
/*  This program is distributed in the hope that it will be useful,      */
/*  but WITHOUT ANY WARRANTY; without even the implied warranty of       */
/*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the         */
/*  GNU General Public License for more details.                         */
/*                                                                       */
/*  You should have received a copy of the GNU General Public License    */
/*  along with this program.  If not, see <http://www.gnu.org/licenses/>.*/
/*************************************************************************/
/*  Initialize the connector in your initialize() method using           */
/*     webCoRE_init()                                                    */
/*  Optionally, pass the string name of a method to call when a piston   */
/*  is executed:                                                         */
/*     webCoRE_init('pistonExecutedMethod')                              */
/*************************************************************************/
/*  List all available pistons by using one of the following:            */
/*     webCoRE_list() - returns the list of id/name pairs                */
/*     webCoRE_list('id') - returns the list of piston IDs               */
/*     webCoRE_list('name') - returns the list of piston names           */
/*************************************************************************/
/*  Execute a piston by using the following:                             */
/*     webCoRE_execute(pistonIdOrName)                                   */
/*  The execute method accepts either an id or the name of a             */
/*  piston, previously retrieved by webCoRE_list()                       */
/*************************************************************************/
private webCoRE_handle(){return'webCoRE'}
private webCoRE_init(pistonExecutedCbk)
{
    state.webCoRE=(state.webCoRE instanceof Map?state.webCoRE:[:])+(pistonExecutedCbk?[cbk:pistonExecutedCbk]:[:]);
    subscribe(location,"${webCoRE_handle()}.pistonList",webCoRE_handler);
    if(pistonExecutedCbk)subscribe(location,"${webCoRE_handle()}.pistonExecuted",webCoRE_handler);webCoRE_poll();
}
private webCoRE_poll(){sendLocationEvent([name: webCoRE_handle(),value:'poll',isStateChange:true,displayed:false])}
public  webCoRE_execute(pistonIdOrName,Map data=[:]){def i=(state.webCoRE?.pistons?:[]).find{(it.name==pistonIdOrName)||(it.id==pistonIdOrName)}?.id;if(i){sendLocationEvent([name:i,value:app.label,isStateChange:true,displayed:false,data:data])}}
public  webCoRE_list(mode)
{
	def p=state.webCoRE?.pistons;
    if(p)p.collect{
		mode=='id'?it.id:(mode=='name'?it.name:[id:it.id,name:it.name])
        // log.debug "Reading piston: ${it}"
	}
    return p
}
public  webCoRE_handler(evt){switch(evt.value){case 'pistonList':List p=state.webCoRE?.pistons?:[];Map d=evt.jsonData?:[:];if(d.id&&d.pistons&&(d.pistons instanceof List)){p.removeAll{it.iid==d.id};p+=d.pistons.collect{[iid:d.id]+it}.sort{it.name};state.webCoRE = [updated:now(),pistons:p];};break;case 'pistonExecuted':def cbk=state.webCoRE?.cbk;if(cbk&&evt.jsonData)"$cbk"(evt.jsonData);break;}}
