#include "lcdMenu.h"
#include "lcd.h"
#include "buttons.h"
#include "rfid.h"
#include "zigbee.h"
#include "book.h"
#include "security.h"
#include <avr/interrupt.h>
#include <avr/sleep.h>
#include <avr/pgmspace.h> 

#define UART_BAUD_RATE              9600
#define UART_UBRR                   F_CPU/16/UART_BAUD_RATE-1


int main(void)
{
	lcdInit();
	buttonInit();
    securityInit();
    menuInit();
	bookInit();
	rfidInit();
    zigbeeInit(); 	

    sei();

    set_sleep_mode(SLEEP_MODE_IDLE);
    sleep_enable();
	
#if DEBUG
	user_auth = USER_AUTH_LIBRARIAN;
#endif	

    while(TRUE) {
        ;
    }
    
}

