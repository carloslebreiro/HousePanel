/**
 *  House Map
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
 */
definition(
    name: "House Map",
    namespace: "kewashi",
    author: "Kenneth Washington",
    description: "Creates a visual map of where activity is happening in a house by reading motion sensor activity and keeping a log. Web inquiries are provided.",
    category: "My Apps",
    iconUrl: "https://s3.amazonaws.com/smartapp-icons/Meta/intruder_motion-presence.png",
    iconX2Url: "https://s3.amazonaws.com/smartapp-icons/Meta/intruder_motion-presence@2x.png",
    iconX3Url: "https://s3.amazonaws.com/smartapp-icons/Meta/intruder_motion-presence@2x.png",
    oauth: [displayName: "kewashi motion mapper", displayLink: ""])


preferences {
    section("Control these switches...") {
        input "myswitches", "capability.switch", multiple: true, required: false
    }
	section ("Allow access to these motion sensors...") {
		input "mysensors", "capability.motionSensor", multiple: true, required: false
	}
	section ("Allow access to these contact sensors...") {
		input "mydoors", "capability.contactSensor", multiple: true, required: false
	}
}

mappings {
  path("/switches") {
    action: [
      GET: "getSwitches",
      POST: "getSwitches"
    ]
  }

  path("/sensors") {
    action: [
      GET: "getSensors",
      POST: "getSensors"
    ]
  }
    
  path("/contacts") {
    action: [
      GET: "getContacts",
      POST: "getContacts"
    ]
  }
  
  path("/setswitch") {
     action: [POST: "setSwitch"]
  }
  
  path("/onemotion") {
    action: [
      GET: "getSensor",
      POST: "getSensor"
    ]
  }

  path("/onecontact") {
    action: [
      GET: "getContact",
      POST: "getContact"
    ]
  }

}

def installed() {
	log.debug "Installed with settings: ${settings}"
}

def updated() {
	log.debug "Updated with settings: ${settings}"
}

def getSwitches() {
    def resp = []
    log.debug "getSwitches being called"
    if (myswitches) log.debug "Number of switches = " + myswitches.size()
    if (myswitches) {
      myswitches.each {
        resp << [name: it.displayName, id: it.id, value: it.currentValue("switch")]
      }
    } else {
      resp << [name: "none found", value: "off"]
    }
    return resp
}

def getSensors() {
    def resp = []
    log.debug "getSensors being called "
    if (mysensors) log.debug "Number of sensors = " + mysensors.size()
    mysensors.each {
      resp << [name: it.displayName, id: it.id, value: it.currentValue("motion")]
    }
    return resp
}

def getContacts() {
    def resp = []
    log.debug "getDoors being called"
    if (mydoors) log.debug "Number of doors = " + mydoors.size()
    if (mydoors) {
      mydoors.each {
        resp << [name: it.displayName, id: it.id, value: it.currentValue("contact")]
      }
    } else {
      resp << [name: "none found", value: "closed"]
    }
    return resp
}

def setSwitch() {
    def cmd = params.swvalue
    def swid = params.swid
    log.debug "setSwitch command = $cmd for id = $swid"

    if (myswitches) {
      myswitches.each {
        if (it.id == swid) {
          if (cmd=="on") {
             it.on()
          } else {
            it.off()
          }
        }
      }
    }

}

def getSensor() {
	// def thesensor = request.JSON?.picked
    def thesensor = params.picked
    if (!thesensor) httpError(400, "Sensor name parameter not provided");
    
    def resp = []
    def found = false
    mysensors.each {
    	// check for matching label, id, or name
    	if (it.displayName == thesensor ) {
        	def startDate = new Date() -1
            // def theEvents = it.eventsSince(startDate)
        	def theHistory = it.statesSince("motion", startDate, [max: 20])
        	resp = [name: it.displayName, size: theHistory.size(), history: theHistory ]
            found = true
        }
    }
    
    if ( ! found ) {
	    resp = [error: "Error finding sensor", name: thesensor];
	    httpError(400, "Sensor not found with id or name: $thesensor");
    }

	return resp
}

def getContact() {
	// def thesensor = request.JSON?.picked
    def thesensor = params.picked
    if (!thesensor) httpError(400, "Contact name parameter not provided");
    
    def resp = []
    def found = false
    mydoors.each {
    	// check for matching label, id, or name
        // log.debug "contact name = " + it.displayName
    	if (it.displayName == thesensor ) {
        	def startDate = new Date() -1
            // def theEvents = it.eventsSince(startDate)
        	def theHistory = it.statesSince("contact", startDate, [max: 20])
        	resp = [name: it.displayName, size: theHistory.size(), history: theHistory ]
            found = true
        }
    }
    
    if ( ! found ) {
	    resp = [error: "Error finding contact", name: thesensor];
	    httpError(400, "Contact not found with id or name: $thesensor");
    }

	return resp
}
