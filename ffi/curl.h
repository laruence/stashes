#define FFI_LIB "libcurl.so"
#define FFI_SCOPE "libcurl"

void *curl_easy_init();
int curl_easy_setopt(void *curl, int option, ...);
int curl_easy_perform(void *curl);
void curl_easy_cleanup(void *handle);
