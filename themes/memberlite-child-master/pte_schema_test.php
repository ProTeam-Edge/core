<?php

include('/var/www/html/proteamedge/public/wp-blog-header.php');
require('/var/www/html/proteamedge/public/wp-content/themes/memberlite-child-master/vendor/autoload.php');


$schema = '{
  "schema": "http://json-schema.org/draft-04/schema#",
  "id": "http://hl7.org/fhir/json-schema/Person",
  "ref": "#/definitions/Person",
  "description": "see http://hl7.org/fhir/json.html#schema for information about the FHIR Json Schemas",
  "definitions": {
    "Person": {
      "allOf": [
        {
          "ref": "DomainResource#/definitions/DomainResource"
        },
        {
          "description": "Demographics and administrative information about a person independent of a specific health-related context.",
          "properties": {
            "resourceType": {
              "description": "This is a Person resource",
              "type": "string",
              "enum": [
                "Person"
              ]
            },
            "identifier": {
              "description": "Identifier for a person within a particular scope.",
              "type": "array",
              "items": {
                "ref": "http://hl7.org/fhir/json-schema/Identifier#/definitions/Identifier"
              }
            },
            "name": {
              "description": "A name associated with the person.",
              "type": "array",
              "items": {
                "ref": "http://hl7.org/fhir/json-schema/HumanName#/definitions/HumanName"
              }
            },
            "telecom": {
              "description": "A contact detail for the person, e.g. a telephone number or an email address.",
              "type": "array",
              "items": {
                "ref": "http://hl7.org/fhir/json-schema/ContactPoint#/definitions/ContactPoint"
              }
            },
            "gender": {
              "description": "Administrative Gender.",
              "enum": [
                "male",
                "female",
                "other",
                "unknown"
              ],
              "type": "string"
            },
            "_gender": {
              "description": "Extensions for gender",
              "ref": "http://hl7.org/fhir/json-schema/Element#/definitions/Element"
            },
            "birthDate": {
              "description": "The birth date for the person.",
              "type": "string",
              "pattern": "-?[0-9]{4}(-(0[1-9]|1[0-2])(-(0[0-9]|[1-2][0-9]|3[0-1]))?)?"
            },
            "_birthDate": {
              "description": "Extensions for birthDate",
              "ref": "http://hl7.org/fhir/json-schema/Element#/definitions/Element"
            },
            "address": {
              "description": "One or more addresses for the person.",
              "type": "array",
              "items": {
                "ref": "http://hl7.org/fhir/json-schema/Address#/definitions/Address"
              }
            },
            "photo": {
              "description": "An image that can be displayed as a thumbnail of the person to enhance the identification of the individual.",
              "ref": "http://hl7.org/fhir/json-schema/Attachment#/definitions/Attachment"
            },
            "managingOrganization": {
              "description": "The organization that is the custodian of the person record.",
              "ref": "http://hl7.org/fhir/json-schema/Reference#/definitions/Reference"
            },
            "active": {
              "description": "Whether this person\u0027s record is in active use.",
              "type": "boolean"
            },
            "_active": {
              "description": "Extensions for active",
              "ref": "http://hl7.org/fhir/json-schema/Element#/definitions/Element"
            },
            "link": {
              "description": "Link to a resource that concerns the same actual person.",
              "type": "array",
              "items": {
                "ref": "#/definitions/Person_Link"
              }
            }
          },
          "required": [
            "resourceType"
          ]
        }
      ]
    },
    "Person_Link": {
      "allOf": [
        {
          "ref": "BackboneElement#/definitions/BackboneElement"
        },
        {
          "description": "Demographics and administrative information about a person independent of a specific health-related context.",
          "properties": {
            "target": {
              "description": "The resource to which this actual person is associated.",
              "ref": "http://hl7.org/fhir/json-schema/Reference#/definitions/Reference"
            },
            "assurance": {
              "description": "Level of assurance that this link is actually associated with the target resource.",
              "enum": [
                "level1",
                "level2",
                "level3",
                "level4"
              ],
              "type": "string"
            },
            "_assurance": {
              "description": "Extensions for assurance",
              "ref": "http://hl7.org/fhir/json-schema/Element#/definitions/Element"
            }
          },
          "required": [
            "target"
          ]
        }
      ]
    }
  }
}';

$schema = '
{
  "resourceType": "Patient",
  "id": "example",
  "text": {
    "status": "generated",
    "div": "<div xmlns=\"http://www.w3.org/1999/xhtml\">\n\t\t\t<table>\n\t\t\t\t<tbody>\n\t\t\t\t\t<tr>\n\t\t\t\t\t\t<td>Name</td>\n\t\t\t\t\t\t<td>Peter James \n              <b>Chalmers</b> (&quot;Jim&quot;)\n            </td>\n\t\t\t\t\t</tr>\n\t\t\t\t\t<tr>\n\t\t\t\t\t\t<td>Address</td>\n\t\t\t\t\t\t<td>534 Erewhon, Pleasantville, Vic, 3999</td>\n\t\t\t\t\t</tr>\n\t\t\t\t\t<tr>\n\t\t\t\t\t\t<td>Contacts</td>\n\t\t\t\t\t\t<td>Home: unknown. Work: (03) 5555 6473</td>\n\t\t\t\t\t</tr>\n\t\t\t\t\t<tr>\n\t\t\t\t\t\t<td>Id</td>\n\t\t\t\t\t\t<td>MRN: 12345 (Acme Healthcare)</td>\n\t\t\t\t\t</tr>\n\t\t\t\t</tbody>\n\t\t\t</table>\n\t\t</div>"
  },
  "identifier": [
    {
      "use": "usual",
      "type": {
        "coding": [
          {
            "system": "http://terminology.hl7.org/CodeSystem/v2-0203",
            "code": "MR"
          }
        ]
      },
      "system": "urn:oid:1.2.36.146.595.217.0.1",
      "value": "12345",
      "period": {
        "start": "2001-05-06"
      },
      "assigner": {
        "display": "Acme Healthcare"
      }
    }
  ],
  "active": true,
  "name": [
    {
      "use": "official",
      "family": "Chalmers",
      "given": [
        "Peter",
        "James"
      ]
    },
    {
      "use": "usual",
      "given": [
        "Jim"
      ]
    },
    {
      "use": "maiden",
      "family": "Windsor",
      "given": [
        "Peter",
        "James"
      ],
      "period": {
        "end": "2002"
      }
    }
  ],
  "telecom": [
    {
      "use": "home"
    },
    {
      "system": "phone",
      "value": "(03) 5555 6473",
      "use": "work",
      "rank": 1
    },
    {
      "system": "phone",
      "value": "(03) 3410 5613",
      "use": "mobile",
      "rank": 2
    },
    {
      "system": "phone",
      "value": "(03) 5555 8834",
      "use": "old",
      "period": {
        "end": "2014"
      }
    }
  ],
  "gender": "male",
  "birthDate": "1974-12-25",
  "_birthDate": {
    "extension": [
      {
        "url": "http://hl7.org/fhir/StructureDefinition/patient-birthTime",
        "valueDateTime": "1974-12-25T14:35:45-05:00"
      }
    ]
  },
  "deceasedBoolean": false,
  "address": [
    {
      "use": "home",
      "type": "both",
      "text": "534 Erewhon St PeasantVille, Rainbow, Vic  3999",
      "line": [
        "534 Erewhon St"
      ],
      "city": "PleasantVille",
      "district": "Rainbow",
      "state": "Vic",
      "postalCode": "3999",
      "period": {
        "start": "1974-12-25"
      }
    }
  ],
  "contact": [
    {
      "relationship": [
        {
          "coding": [
            {
              "system": "http://terminology.hl7.org/CodeSystem/v2-0131",
              "code": "N"
            }
          ]
        }
      ],
      "name": {
        "family": "du Marché",
        "_family": {
          "extension": [
            {
              "url": "http://hl7.org/fhir/StructureDefinition/humanname-own-prefix",
              "valueString": "VV"
            }
          ]
        },
        "given": [
          "Bénédicte"
        ]
      },
      "telecom": [
        {
          "system": "phone",
          "value": "+33 (237) 998327"
        }
      ],
      "address": {
        "use": "home",
        "type": "both",
        "line": [
          "534 Erewhon St"
        ],
        "city": "PleasantVille",
        "district": "Rainbow",
        "state": "Vic",
        "postalCode": "3999",
        "period": {
          "start": "1974-12-25"
        }
      },
      "gender": "female",
      "period": {
        "start": "2012"
      }
    }
  ],
  "managingOrganization": {
    "reference": "Organization/1"
  }
}
';


$schema ='
{
  "resourceType": "Patient",
  "id": "pat1",
  "text": {
    "status": "generated",
    "div": "\u003cdiv xmlns\u003d\"http://www.w3.org/1999/xhtml\"\u003e\n      \n      \u003cp\u003ePatient Donald DUCK @ Acme Healthcare, Inc. MR \u003d 654321\u003c/p\u003e\n    \n    \u003c/div\u003e"
  },
  "identifier": [
    {
      "use": "usual",
      "type": {
        "coding": [
          {
            "system": "http://terminology.hl7.org/CodeSystem/v2-0203",
            "code": "MR"
          }
        ]
      },
      "system": "urn:oid:0.1.2.3.4.5.6.7",
      "value": "654321"
    }
  ],
  "active": true,
  "name": [
    {
      "use": "official",
      "family": "Donald",
      "given": [
        "Duck"
      ]
    }
  ],
  "gender": "male",
  "photo": [
    {
      "contentType": "image/gif",
      "data": "R0lGODlhEwARAPcAAAAAAAAA/+9aAO+1AP/WAP/eAP/eCP/eEP/eGP/nAP/nCP/nEP/nIf/nKf/nUv/nWv/vAP/vCP/vEP/vGP/vIf/vKf/vMf/vOf/vWv/vY//va//vjP/3c//3lP/3nP//tf//vf///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////yH5BAEAAAEALAAAAAATABEAAAi+AAMIDDCgYMGBCBMSvMCQ4QCFCQcwDBGCA4cLDyEGECDxAoAQHjxwyKhQAMeGIUOSJJjRpIAGDS5wCDly4AALFlYOgHlBwwOSNydM0AmzwYGjBi8IHWoTgQYORg8QIGDAwAKhESI8HIDgwQaRDI1WXXAhK9MBBzZ8/XDxQoUFZC9IiCBh6wEHGz6IbNuwQoSpWxEgyLCXL8O/gAnylNlW6AUEBRIL7Og3KwQIiCXb9HsZQoIEUzUjNEiaNMKAAAA7"
    }
  ],
  "contact": [
    {
      "relationship": [
        {
          "coding": [
            {
              "system": "http://terminology.hl7.org/CodeSystem/v2-0131",
              "code": "E"
            }
          ]
        }
      ],
      "organization": {
        "reference": "Organization/1",
        "display": "Walt Disney Corporation"
      }
    }
  ],
  "managingOrganization": {
    "reference": "Organization/1",
    "display": "ACME Healthcare, Inc"
  },
  "link": [
    {
      "other": {
        "reference": "Patient/pat2"
      },
      "type": "seealso"
    }
  ],
  "meta": {
    "tag": [
      {
        "system": "http://terminology.hl7.org/CodeSystem/v3-ActReason",
        "code": "HTEST",
        "display": "test health data"
      }
    ]
  }
}
';


$schema = '
{
  "resourceType": "Bundle",
  "id": "b248b1b2-1686-4b94-9936-37d7a5f94b51",
  "meta": {
    "lastUpdated": "2012-05-29T23:45:32Z",
    "tag": [
      {
        "system": "http://terminology.hl7.org/CodeSystem/v3-ActReason",
        "code": "HTEST",
        "display": "test health data"
      }
    ]
  },
  "type": "collection",
  "entry": [
    {
      "fullUrl": "http://hl7.org/fhir/Patient/1",
      "resource": {
        "resourceType": "Patient",
        "id": "1",
        "meta": {
          "lastUpdated": "2012-05-29T23:45:32Z"
        },
        "text": {
          "status": "generated",
          "div": "\u003cdiv xmlns\u003d\"http://www.w3.org/1999/xhtml\"\u003eEverywoman, Eve. SSN:\n            444222222\u003c/div\u003e"
        },
        "identifier": [
          {
            "type": {
              "coding": [
                {
                  "system": "http://terminology.hl7.org/CodeSystem/v2-0203",
                  "code": "SS"
                }
              ]
            },
            "system": "http://hl7.org/fhir/sid/us-ssn",
            "value": "444222222"
          }
        ],
        "active": true,
        "name": [
          {
            "use": "official",
            "family": "Everywoman",
            "given": [
              "Eve"
            ]
          }
        ],
        "telecom": [
          {
            "system": "phone",
            "value": "555-555-2003",
            "use": "work"
          }
        ],
        "gender": "female",
        "birthDate": "1973-05-31",
        "address": [
          {
            "use": "home",
            "line": [
              "2222 Home Street"
            ]
          }
        ],
        "managingOrganization": {
          "reference": "Organization/hl7"
        }
      }
    },
    {
      "fullUrl": "http://hl7.org/fhir/Patient/2",
      "resource": {
        "resourceType": "Patient",
        "id": "2",
        "meta": {
          "lastUpdated": "2012-05-29T23:45:32Z"
        },
        "text": {
          "status": "generated",
          "div": "\u003cdiv xmlns\u003d\"http://www.w3.org/1999/xhtml\"\u003eEveryman, Adam. SSN:\n            444333333\u003c/div\u003e"
        },
        "identifier": [
          {
            "type": {
              "coding": [
                {
                  "system": "http://terminology.hl7.org/CodeSystem/v2-0203",
                  "code": "SS"
                }
              ]
            },
            "system": "http://hl7.org/fhir/sid/us-ssn",
            "value": "444333333"
          }
        ],
        "active": true,
        "name": [
          {
            "use": "official",
            "family": "Everyman",
            "given": [
              "Adam"
            ]
          }
        ],
        "telecom": [
          {
            "system": "phone",
            "value": "555-555-2004",
            "use": "work"
          }
        ],
        "gender": "male",
        "address": [
          {
            "use": "home",
            "line": [
              "2222 Home Street"
            ]
          }
        ],
        "managingOrganization": {
          "reference": "Organization/hl7"
        }
      }
    },
    {
      "fullUrl": "http://hl7.org/fhir/Patient/3",
      "resource": {
        "resourceType": "Patient",
        "id": "3",
        "meta": {
          "lastUpdated": "2012-05-29T23:45:32Z"
        },
        "text": {
          "status": "generated",
          "div": "\u003cdiv xmlns\u003d\"http://www.w3.org/1999/xhtml\"\u003eKidd, Kari. SSN:\n            444555555\u003c/div\u003e"
        },
        "identifier": [
          {
            "type": {
              "coding": [
                {
                  "system": "http://terminology.hl7.org/CodeSystem/v2-0203",
                  "code": "SS"
                }
              ]
            },
            "system": "http://hl7.org/fhir/sid/us-ssn",
            "value": "444555555"
          }
        ],
        "active": true,
        "name": [
          {
            "use": "official",
            "family": "Kidd",
            "given": [
              "Kari"
            ]
          }
        ],
        "telecom": [
          {
            "system": "phone",
            "value": "555-555-2005",
            "use": "work"
          }
        ],
        "gender": "female",
        "address": [
          {
            "use": "home",
            "line": [
              "2222 Home Street"
            ]
          }
        ],
        "managingOrganization": {
          "reference": "Organization/hl7"
        }
      }
    },
    {
      "fullUrl": "http://hl7.org/fhir/Patient/4",
      "resource": {
        "resourceType": "Patient",
        "id": "4",
        "meta": {
          "lastUpdated": "2012-05-29T23:45:32Z"
        },
        "text": {
          "status": "generated",
          "div": "\u003cdiv xmlns\u003d\"http://www.w3.org/1999/xhtml\"\u003eNuclear, Nancy. SSN:\n            444114567\u003c/div\u003e"
        },
        "identifier": [
          {
            "type": {
              "coding": [
                {
                  "system": "http://terminology.hl7.org/CodeSystem/v2-0203",
                  "code": "SS"
                }
              ]
            },
            "system": "http://hl7.org/fhir/sid/us-ssn",
            "value": "444114567"
          }
        ],
        "active": true,
        "name": [
          {
            "use": "official",
            "family": "Nuclear",
            "given": [
              "Nancy"
            ]
          }
        ],
        "telecom": [
          {
            "system": "phone",
            "value": "555-555-5001",
            "use": "work"
          }
        ],
        "gender": "female",
        "address": [
          {
            "use": "home",
            "line": [
              "6666 Home Street"
            ]
          }
        ],
        "managingOrganization": {
          "reference": "Organization/hl7"
        }
      }
    },
    {
      "fullUrl": "http://hl7.org/fhir/Patient/5",
      "resource": {
        "resourceType": "Patient",
        "id": "5",
        "meta": {
          "lastUpdated": "2012-05-29T23:45:32Z"
        },
        "text": {
          "status": "generated",
          "div": "\u003cdiv xmlns\u003d\"http://www.w3.org/1999/xhtml\"\u003eNuclear, Neville. SSN:\n            444111234\u003c/div\u003e"
        },
        "identifier": [
          {
            "type": {
              "coding": [
                {
                  "system": "http://terminology.hl7.org/CodeSystem/v2-0203",
                  "code": "SS"
                }
              ]
            },
            "system": "http://hl7.org/fhir/sid/us-ssn",
            "value": "444111234"
          }
        ],
        "active": true,
        "name": [
          {
            "use": "official",
            "family": "Nuclear",
            "given": [
              "Neville"
            ]
          }
        ],
        "telecom": [
          {
            "system": "phone",
            "value": "555-555-5001",
            "use": "work"
          }
        ],
        "gender": "male",
        "address": [
          {
            "use": "home",
            "line": [
              "6666 Home Street"
            ]
          }
        ],
        "managingOrganization": {
          "reference": "Organization/hl7"
        }
      }
    },
    {
      "fullUrl": "http://hl7.org/fhir/Patient/6",
      "resource": {
        "resourceType": "Patient",
        "id": "6",
        "meta": {
          "lastUpdated": "2012-05-29T23:45:32Z"
        },
        "text": {
          "status": "generated",
          "div": "\u003cdiv xmlns\u003d\"http://www.w3.org/1999/xhtml\"\u003eNuclear, Ned. SSN:\n            444113456\u003c/div\u003e"
        },
        "identifier": [
          {
            "type": {
              "coding": [
                {
                  "system": "http://terminology.hl7.org/CodeSystem/v2-0203",
                  "code": "SS"
                }
              ]
            },
            "system": "http://hl7.org/fhir/sid/us-ssn",
            "value": "444113456"
          }
        ],
        "active": true,
        "name": [
          {
            "use": "official",
            "family": "Nuclear",
            "given": [
              "Ned"
            ]
          }
        ],
        "telecom": [
          {
            "system": "phone",
            "value": "555-555-5001",
            "use": "work"
          }
        ],
        "gender": "male",
        "address": [
          {
            "use": "home",
            "line": [
              "6666 Home Street"
            ]
          }
        ],
        "managingOrganization": {
          "reference": "Organization/hl7"
        }
      }
    },
    {
      "fullUrl": "http://hl7.org/fhir/Patient/7",
      "resource": {
        "resourceType": "Patient",
        "id": "7",
        "meta": {
          "lastUpdated": "2012-05-29T23:45:32Z"
        },
        "text": {
          "status": "generated",
          "div": "\u003cdiv xmlns\u003d\"http://www.w3.org/1999/xhtml\"\u003eNuclear, Nelda. SSN:\n            444112345\u003c/div\u003e"
        },
        "identifier": [
          {
            "type": {
              "coding": [
                {
                  "system": "http://terminology.hl7.org/CodeSystem/v2-0203",
                  "code": "SS"
                }
              ]
            },
            "system": "http://hl7.org/fhir/sid/us-ssn",
            "value": "444112345"
          }
        ],
        "active": true,
        "name": [
          {
            "use": "official",
            "family": "Nuclear",
            "given": [
              "Nelda"
            ]
          }
        ],
        "telecom": [
          {
            "system": "phone",
            "value": "555-555-5001",
            "use": "work"
          }
        ],
        "gender": "female",
        "address": [
          {
            "use": "home",
            "line": [
              "6666 Home Street"
            ]
          }
        ],
        "managingOrganization": {
          "reference": "Organization/hl7"
        }
      }
    },
    {
      "fullUrl": "http://hl7.org/fhir/Patient/8",
      "resource": {
        "resourceType": "Patient",
        "id": "8",
        "meta": {
          "lastUpdated": "2012-05-29T23:45:32Z"
        },
        "text": {
          "status": "generated",
          "div": "\u003cdiv xmlns\u003d\"http://www.w3.org/1999/xhtml\"\u003eMum, Martha. SSN:\n            444666666\u003c/div\u003e"
        },
        "identifier": [
          {
            "type": {
              "coding": [
                {
                  "system": "http://terminology.hl7.org/CodeSystem/v2-0203",
                  "code": "SS"
                }
              ]
            },
            "system": "http://hl7.org/fhir/sid/us-ssn",
            "value": "444666666"
          }
        ],
        "active": true,
        "name": [
          {
            "use": "official",
            "family": "Mum",
            "given": [
              "Martha"
            ]
          }
        ],
        "telecom": [
          {
            "system": "phone",
            "value": "555-555-2006",
            "use": "work"
          }
        ],
        "gender": "female",
        "address": [
          {
            "use": "home",
            "line": [
              "4444 Home Street"
            ]
          }
        ],
        "managingOrganization": {
          "reference": "Organization/hl7"
        }
      }
    },
    {
      "fullUrl": "http://hl7.org/fhir/Patient/9",
      "resource": {
        "resourceType": "Patient",
        "id": "9",
        "meta": {
          "lastUpdated": "2012-05-29T23:45:32Z"
        },
        "text": {
          "status": "generated",
          "div": "\u003cdiv xmlns\u003d\"http://www.w3.org/1999/xhtml\"\u003eSons, Stuart. SSN:\n            444777777\u003c/div\u003e"
        },
        "identifier": [
          {
            "type": {
              "coding": [
                {
                  "system": "http://terminology.hl7.org/CodeSystem/v2-0203",
                  "code": "SS"
                }
              ]
            },
            "system": "http://hl7.org/fhir/sid/us-ssn",
            "value": "444777777"
          }
        ],
        "active": true,
        "name": [
          {
            "use": "official",
            "family": "Sons",
            "given": [
              "Stuart"
            ]
          }
        ],
        "telecom": [
          {
            "system": "phone",
            "value": "555-555-2007",
            "use": "work"
          }
        ],
        "gender": "other",
        "_gender": {
          "extension": [
            {
              "url": "http://example.org/Profile/administrative-status",
              "valueCodeableConcept": {
                "coding": [
                  {
                    "system": "http://example.org/fhir/v2/administrative-status",
                    "code": "FTM",
                    "display": "female-to-male"
                  }
                ]
              }
            }
          ]
        },
        "deceasedDateTime": "2002-08-24",
        "address": [
          {
            "use": "home",
            "line": [
              "4444 Home Street"
            ]
          }
        ],
        "managingOrganization": {
          "reference": "Organization/hl7"
        }
      }
    },
    {
      "fullUrl": "http://hl7.org/fhir/Patient/10",
      "resource": {
        "resourceType": "Patient",
        "id": "10",
        "meta": {
          "lastUpdated": "2012-05-29T23:45:32Z"
        },
        "text": {
          "status": "generated",
          "div": "\u003cdiv xmlns\u003d\"http://www.w3.org/1999/xhtml\"\u003eBetterhalf, Boris. SSN:\n            444888888\u003c/div\u003e"
        },
        "identifier": [
          {
            "type": {
              "coding": [
                {
                  "system": "http://terminology.hl7.org/CodeSystem/v2-0203",
                  "code": "SS"
                }
              ]
            },
            "system": "http://hl7.org/fhir/sid/us-ssn",
            "value": "444888888"
          }
        ],
        "active": true,
        "name": [
          {
            "use": "official",
            "family": "Betterhalf",
            "given": [
              "Boris"
            ]
          }
        ],
        "telecom": [
          {
            "system": "phone",
            "value": "555-555-2008",
            "use": "work"
          }
        ],
        "gender": "male",
        "address": [
          {
            "use": "home",
            "line": [
              "2222 Home Street"
            ]
          }
        ],
        "managingOrganization": {
          "reference": "Organization/hl7"
        }
      }
    },
    {
      "fullUrl": "http://hl7.org/fhir/Patient/11",
      "resource": {
        "resourceType": "Patient",
        "id": "11",
        "meta": {
          "lastUpdated": "2012-05-29T23:45:32Z"
        },
        "text": {
          "status": "generated",
          "div": "\u003cdiv xmlns\u003d\"http://www.w3.org/1999/xhtml\"\u003eRelative, Ralph. SSN:\n            444999999\u003c/div\u003e"
        },
        "identifier": [
          {
            "type": {
              "coding": [
                {
                  "system": "http://terminology.hl7.org/CodeSystem/v2-0203",
                  "code": "SS"
                }
              ]
            },
            "system": "http://hl7.org/fhir/sid/us-ssn",
            "value": "444999999"
          }
        ],
        "active": true,
        "name": [
          {
            "use": "official",
            "family": "Relative",
            "given": [
              "Ralph"
            ]
          }
        ],
        "telecom": [
          {
            "system": "phone",
            "value": "555-555-2009",
            "use": "work"
          }
        ],
        "gender": "male",
        "address": [
          {
            "use": "home",
            "line": [
              "4444 Home Street"
            ]
          }
        ],
        "managingOrganization": {
          "reference": "Organization/hl7"
        }
      }
    },
    {
      "fullUrl": "http://hl7.org/fhir/Patient/12",
      "resource": {
        "resourceType": "Patient",
        "id": "12",
        "meta": {
          "lastUpdated": "2012-05-29T23:45:32Z"
        },
        "text": {
          "status": "generated",
          "div": "\u003cdiv xmlns\u003d\"http://www.w3.org/1999/xhtml\"\u003eContact, Carrie. SSN:\n            555222222\u003c/div\u003e"
        },
        "identifier": [
          {
            "type": {
              "coding": [
                {
                  "system": "http://terminology.hl7.org/CodeSystem/v2-0203",
                  "code": "SS"
                }
              ]
            },
            "system": "http://hl7.org/fhir/sid/us-ssn",
            "value": "555222222"
          }
        ],
        "active": true,
        "name": [
          {
            "use": "official",
            "family": "Contact",
            "given": [
              "Carrie"
            ]
          }
        ],
        "telecom": [
          {
            "system": "phone",
            "value": "555-555-2010",
            "use": "work"
          }
        ],
        "gender": "female",
        "address": [
          {
            "use": "home",
            "line": [
              "5555 Home Street"
            ]
          }
        ],
        "managingOrganization": {
          "reference": "Organization/hl7"
        }
      }
    }
  ]
}
';

use NestedJsonFlattener\Flattener\Flattener;

$flattener = new Flattener();
$flattener->setJsonData($schema);
$flat = $flattener->getFlatData();
pp($flat);



