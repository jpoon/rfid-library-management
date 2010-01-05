#include "rfid.h"
#include "zigbee.h"
#include <stdlib.h> 
#include "util.h"
#include "lcd.h"
#include <avr/io.h>

volatile BYTE isCheckedOut;

void securityInit ( void ) {
	BUZZER_DDR |= _BV(BUZZER);
}

void soundAlarm( int length ) {
	BUZZER_PORT |= _BV(BUZZER);
	delay_ms(length);
	BUZZER_PORT	&= ~_BV(BUZZER);		
}

void securityCheck ( void ) {
	BYTE* pTagNumber;

	while(!userCancelRequest) {
		pTagNumber = rfidscanBook();
		if (userCancelRequest) return;

		if (pTagNumber != NULL) {
			zigbeeSendCmd(ZIGBEE_CMD_SECURITY_REQ, 0, pTagNumber);
			
			while (!packetReceived) {
				if (userCancelRequest) return;
			}
			packetReceived = FALSE;

			lcdGoTo(2,1);
			if (isCheckedOut != BOOK_STATE_CHECKOUT) {
				printf("Stop! Thief!");

				soundAlarm(1000);
				delay_ms(1000);				
				soundAlarm(1000);
				delay_ms(1000);				
				soundAlarm(1000);
				
			} else {			
				printf("Thank You");			
			}
		}
	}
}
