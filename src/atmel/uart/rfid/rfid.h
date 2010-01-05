#ifndef _RFID_H_
#define _RFID_H_

#include "global.h"

#define MAX_RFID_PACKET_SIZE 		32
#define RFID_UART_TX_BUFFER_MASK 	( MAX_RFID_PACKET_SIZE - 1)

void rfidInit( void );
BOOL rfidFindBook( BYTE* );
BYTE* rfidscanBook ( void );

#endif /* _UART_H_ */
