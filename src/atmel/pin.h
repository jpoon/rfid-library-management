#ifndef _PIN_H_
#define _PIN_H_

#include "global.h"

/* Port A */
#define LCD_DATA_PIN				PINA
#define LCD_DATA_DDR				DDRA
#define LCD_DATA_PORT   			PORTA		    // PA 7-0

/* Port C */
#define BUTTONS_DDR					DDRC
#define BUTTONS_PORT				PORTC
#define BUTTONS_PIN					PINC

#define BUTTON_LEFT					0				// PC 0
#define BUTTON_RIGHT				1				// PC 1
#define BUTTON_UP					3				// PC 3
#define BUTTON_DOWN					2				// PC 2

#define BUZZER_DDR                  DDRC
#define BUZZER_PORT                 PORTC

#define BUZZER                      6               // PC 6

/* Port D */
#define SW_INTERRUPT_PORT			PORTD
#define	SW_INTERRUPT_ISR			INT0_vect		
#define SW_INTERRUPT_ZIGBEE			2				// PD2


/* Port E */
#define LCD_CTRL_DDR    			DDRE
#define LCD_CTRL_PORT				PORTE

#define LCD_RS_PIN      			0				// PE 0
#define LCD_RW_PIN      			1            	// PE 1
#define LCD_EN_PIN      			2            	// PE 2


/* UART */
#define ZIGBEE_UART					0				// UART 0 (PB2, PB3)
#define ZIGBEE_UBRRH				UBRR0H
#define ZIGBEE_UBRRL				UBRR0L
#define ZIGBEE_UART_CONTROL_A		UCSR0A				
#define ZIGBEE_UART_CONTROL_B		UCSR0B
#define ZIGBEE_UART_CONTROL_C		UCSR0C
#define ZIGBEE_UART_DATA 			UDR0
#define ZIGBEE_UART_UDRIE			UDRIE0
#define ZIGBEE_UART_RX_INTERRUPT    SIG_USART0_RECV
#define ZIGBEE_UART_TX_INTERRUPT    SIG_USART0_DATA

#define RFID_UART					1				// UART 1 (PD0, PD1)
#define RFID_UART_STATUS 			UCSR1A				
#define RFID_UART_CONTROL_B			UCSR1B
#define RFID_UART_CONTROL_C			UCSR1C
#define RFID_UART_DATA   			UDR1
#define RFID_UART_UDRIE  			UDRIE1
#define RFID_UART_RX_INTERRUPT     	SIG_USART1_RECV
#define RFID_UART_TX_INTERRUPT    	SIG_USART1_DATA

#endif /* _PIN_H_ */
