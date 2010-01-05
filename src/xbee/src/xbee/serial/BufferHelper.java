public class BufferHelper {
	
	public static int testNum = 10;

	public static long bytesToLong( int[] data, int len ){
		
		long RFIDnum = 0;
		
		for( int i = 0; i < data.length; i++ ){
			RFIDnum = RFIDnum << 8; //shift left by two HEX digits
			RFIDnum += data[i];
		}
		
		return RFIDnum;
	}
	
	public static int[] longToBytes(long num){
		
		int[] myBytes = new int[8];
		int myByte = 0;
		
		for( int i = 7 ; i >= 0; i--){
			myByte = (int) (num & 0x0FF);
			myBytes[i] = myByte;
			num = num >> 8;
		}
		
		return myBytes;
	}
	
	public static int[] intToBytes(int num){ 
		int[] myBytes = new int[4];
		int myByte = 0;
		
		for( int i = 3 ; i >= 0; i--){
			myByte = (int) (num & 0x0FF);
			myBytes[i] = myByte;
			num = num >> 8;
		}
		
		return myBytes;
	}
	
	public static int[] getUserID(int num){
		int[] myBytes = new int[2];
		int myByte = 0;
		
		for( int i = 1; i >= 0; i-- ){
			myByte = num & 0xFF;
			myBytes[i] = myByte;
			num = num >> 8;
		}
		
		return myBytes;
	}
	
	public static int bytesToByte(int[] data){
		int num = 0;
		
		for( int i = 0; i < data.length; i++ ){
			num = num << 8; //shift left by two HEX digits
			num += data[i];
		}
		
		return num;
	}
}
