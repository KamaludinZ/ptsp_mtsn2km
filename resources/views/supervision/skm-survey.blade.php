<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Survei Kepuasan Masyarakat') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div class="text-center mb-8">
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">
                            Survei Kepuasan Masyarakat (SKM)
                        </h1>
                        <p class="text-gray-600 dark:text-gray-300 max-w-2xl mx-auto">
                            Pendapat Anda sangat penting bagi kami untuk meningkatkan kualitas pelayanan. 
                            Survei ini bersifat anonim dan hanya memakan waktu kurang dari 5 menit.
                        </p>
                    </div>

                    <!-- Survey Introduction -->
                    <div class="bg-gradient-to-br from-blue-50 to-indigo-50 dark:from-gray-700 dark:to-gray-800 rounded-xl p-8 mb-8">
                        <div class="flex flex-col md:flex-row items-center gap-6">
                            <div class="flex-shrink-0">
                                <div class="bg-blue-100 dark:bg-blue-900 rounded-full p-6">
                                    <i class="fas fa-poll text-blue-600 dark:text-blue-400 text-4xl"></i>
                                </div>
                            </div>
                            <div>
                                <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-3">
                                    Tujuan Survei
                                </h2>
                                <p class="text-gray-700 dark:text-gray-300 mb-4">
                                    Survei Kepuasan Masyarakat (SKM) ini dilaksanakan sesuai dengan Peraturan Menteri PANRB Nomor 15 Tahun 2014 
                                    tentang Pedoman Pelayanan Publik untuk mengukur tingkat kepuasan masyarakat terhadap pelayanan yang kami berikan.
                                </p>
                                <div class="flex items-center text-sm text-gray-600 dark:text-gray-400">
                                    <i class="fas fa-lock mr-2"></i>
                                    <span>Data yang Anda berikan bersifat rahasia dan hanya digunakan untuk evaluasi pelayanan</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SKM Survey Form -->
                    <div class="bg-white dark:bg-gray-700 rounded-xl shadow-md overflow-hidden mb-8">
                        <div class="p-6 border-b border-gray-200 dark:border-gray-600">
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                                Kuisioner SKM - Persepsi Terhadap Pelayanan
                            </h3>
                            <p class="text-gray-600 dark:text-gray-300 mt-2">
                                Mohon beri penilaian Anda terhadap pelayanan yang telah diterima
                            </p>
                        </div>

                        <form class="p-6 space-y-8">
                            <!-- Question 1 -->
                            <div class="border-b border-gray-200 dark:border-gray-600 pb-8">
                                <div class="flex flex-col md:flex-row md:items-start gap-4">
                                    <div class="md:w-10 flex-shrink-0 text-center">
                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 font-bold">
                                            1
                                        </span>
                                    </div>
                                    <div class="flex-grow">
                                        <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                                            Bagaimana pendapat Saudara tentang kesesuaian persyaratan pelayanan dengan jenis pelayanan?
                                        </h4>
                                        <div class="space-y-3">
                                            <div class="flex items-center">
                                                <input id="q1-1" type="radio" name="q1" value="1" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                                <label for="q1-1" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">Tidak sesuai</label>
                                            </div>
                                            <div class="flex items-center">
                                                <input id="q1-2" type="radio" name="q1" value="2" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                                <label for="q1-2" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">Kurang sesuai</label>
                                            </div>
                                            <div class="flex items-center">
                                                <input id="q1-3" type="radio" name="q1" value="3" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                                <label for="q1-3" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">Cukup sesuai</label>
                                            </div>
                                            <div class="flex items-center">
                                                <input id="q1-4" type="radio" name="q1" value="4" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                                <label for="q1-4" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">Sesuai</label>
                                            </div>
                                            <div class="flex items-center">
                                                <input id="q1-5" type="radio" name="q1" value="5" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                                <label for="q1-5" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">Sangat sesuai</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Question 2 -->
                            <div class="border-b border-gray-200 dark:border-gray-600 pb-8">
                                <div class="flex flex-col md:flex-row md:items-start gap-4">
                                    <div class="md:w-10 flex-shrink-0 text-center">
                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 font-bold">
                                            2
                                        </span>
                                    </div>
                                    <div class="flex-grow">
                                        <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                                            Bagaimana pendapat Saudara tentang kemudahan prosedur pelayanan di unit pelayanan?
                                        </h4>
                                        <div class="space-y-3">
                                            <div class="flex items-center">
                                                <input id="q2-1" type="radio" name="q2" value="1" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                                <label for="q2-1" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">Sangat rumit</label>
                                            </div>
                                            <div class="flex items-center">
                                                <input id="q2-2" type="radio" name="q2" value="2" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                                <label for="q2-2" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">Rumit</label>
                                            </div>
                                            <div class="flex items-center">
                                                <input id="q2-3" type="radio" name="q2" value="3" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                                <label for="q2-3" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">Cukup mudah</label>
                                            </div>
                                            <div class="flex items-center">
                                                <input id="q2-4" type="radio" name="q2" value="4" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                                <label for="q2-4" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">Mudah</label>
                                            </div>
                                            <div class="flex items-center">
                                                <input id="q2-5" type="radio" name="q2" value="5" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                                <label for="q2-5" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">Sangat mudah</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Question 3 -->
                            <div class="border-b border-gray-200 dark:border-gray-600 pb-8">
                                <div class="flex flex-col md:flex-row md:items-start gap-4">
                                    <div class="md:w-10 flex-shrink-0 text-center">
                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 font-bold">
                                            3
                                        </span>
                                    </div>
                                    <div class="flex-grow">
                                        <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                                            Bagaimana pendapat Saudara tentang kecepatan waktu dalam memberikan pelayanan?
                                        </h4>
                                        <div class="space-y-3">
                                            <div class="flex items-center">
                                                <input id="q3-1" type="radio" name="q3" value="1" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                                <label for="q3-1" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">Sangat lama</label>
                                            </div>
                                            <div class="flex items-center">
                                                <input id="q3-2" type="radio" name="q3" value="2" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                                <label for="q3-2" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">Lama</label>
                                            </div>
                                            <div class="flex items-center">
                                                <input id="q3-3" type="radio" name="q3" value="3" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                                <label for="q3-3" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">Cukup cepat</label>
                                            </div>
                                            <div class="flex items-center">
                                                <input id="q3-4" type="radio" name="q3" value="4" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                                <label for="q3-4" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">Cepat</label>
                                            </div>
                                            <div class="flex items-center">
                                                <input id="q3-5" type="radio" name="q3" value="5" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                                <label for="q3-5" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">Sangat cepat</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Question 4 -->
                            <div class="border-b border-gray-200 dark:border-gray-600 pb-8">
                                <div class="flex flex-col md:flex-row md:items-start gap-4">
                                    <div class="md:w-10 flex-shrink-0 text-center">
                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 font-bold">
                                            4
                                        </span>
                                    </div>
                                    <div class="flex-grow">
                                        <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                                            Bagaimana pendapat Saudara tentang kewajaran biaya/tarif untuk mendapatkan pelayanan?
                                        </h4>
                                        <div class="space-y-3">
                                            <div class="flex items-center">
                                                <input id="q4-1" type="radio" name="q4" value="1" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                                <label for="q4-1" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">Sangat tidak wajar</label>
                                            </div>
                                            <div class="flex items-center">
                                                <input id="q4-2" type="radio" name="q4" value="2" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                                <label for="q4-2" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">Tidak wajar</label>
                                            </div>
                                            <div class="flex items-center">
                                                <input id="q4-3" type="radio" name="q4" value="3" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                                <label for="q4-3" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">Cukup wajar</label>
                                            </div>
                                            <div class="flex items-center">
                                                <input id="q4-4" type="radio" name="q4" value="4" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                                <label for="q4-4" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">Wajar</label>
                                            </div>
                                            <div class="flex items-center">
                                                <input id="q4-5" type="radio" name="q4" value="5" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                                <label for="q4-5" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">Sangat wajar</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Question 5 -->
                            <div class="border-b border-gray-200 dark:border-gray-600 pb-8">
                                <div class="flex flex-col md:flex-row md:items-start gap-4">
                                    <div class="md:w-10 flex-shrink-0 text-center">
                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 font-bold">
                                            5
                                        </span>
                                    </div>
                                    <div class="flex-grow">
                                        <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                                            Bagaimana pendapat Saudara tentang kesopanan dan keramahan petugas dalam memberikan pelayanan?
                                        </h4>
                                        <div class="space-y-3">
                                            <div class="flex items-center">
                                                <input id="q5-1" type="radio" name="q5" value="1" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                                <label for="q5-1" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">Sangat tidak sopan dan tidak ramah</label>
                                            </div>
                                            <div class="flex items-center">
                                                <input id="q5-2" type="radio" name="q5" value="2" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                                <label for="q5-2" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">Tidak sopan dan tidak ramah</label>
                                            </div>
                                            <div class="flex items-center">
                                                <input id="q5-3" type="radio" name="q5" value="3" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                                <label for="q5-3" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">Cukup sopan dan cukup ramah</label>
                                            </div>
                                            <div class="flex items-center">
                                                <input id="q5-4" type="radio" name="q5" value="4" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                                <label for="q5-4" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">Sopan dan ramah</label>
                                            </div>
                                            <div class="flex items-center">
                                                <input id="q5-5" type="radio" name="q5" value="5" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                                <label for="q5-5" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">Sangat sopan dan sangat ramah</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Question 6 -->
                            <div class="border-b border-gray-200 dark:border-gray-600 pb-8">
                                <div class="flex flex-col md:flex-row md:items-start gap-4">
                                    <div class="md:w-10 flex-shrink-0 text-center">
                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 font-bold">
                                            6
                                        </span>
                                    </div>
                                    <div class="flex-grow">
                                        <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                                            Bagaimana pendapat Saudara tentang kualitas sarana dan prasarana di unit pelayanan?
                                        </h4>
                                        <div class="space-y-3">
                                            <div class="flex items-center">
                                                <input id="q6-1" type="radio" name="q6" value="1" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                                <label for="q6-1" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">Buruk</label>
                                            </div>
                                            <div class="flex items-center">
                                                <input id="q6-2" type="radio" name="q6" value="2" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                                <label for="q6-2" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">Kurang baik</label>
                                            </div>
                                            <div class="flex items-center">
                                                <input id="q6-3" type="radio" name="q6" value="3" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                                <label for="q6-3" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">Cukup baik</label>
                                            </div>
                                            <div class="flex items-center">
                                                <input id="q6-4" type="radio" name="q6" value="4" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                                <label for="q6-4" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">Baik</label>
                                            </div>
                                            <div class="flex items-center">
                                                <input id="q6-5" type="radio" name="q6" value="5" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                                <label for="q6-5" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">Sangat baik</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Question 7 -->
                            <div class="pb-8">
                                <div class="flex flex-col md:flex-row md:items-start gap-4">
                                    <div class="md:w-10 flex-shrink-0 text-center">
                                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200 font-bold">
                                            7
                                        </span>
                                    </div>
                                    <div class="flex-grow">
                                        <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                                            Bagaimana pendapat Saudara tentang penyelesaian masalah pelayanan pengaduan?
                                        </h4>
                                        <div class="space-y-3">
                                            <div class="flex items-center">
                                                <input id="q7-1" type="radio" name="q7" value="1" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                                <label for="q7-1" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">Tidak ada penyelesaian</label>
                                            </div>
                                            <div class="flex items-center">
                                                <input id="q7-2" type="radio" name="q7" value="2" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                                <label for="q7-2" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">Penyelesaian kurang baik</label>
                                            </div>
                                            <div class="flex items-center">
                                                <input id="q7-3" type="radio" name="q7" value="3" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                                <label for="q7-3" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">Cukup baik</label>
                                            </div>
                                            <div class="flex items-center">
                                                <input id="q7-4" type="radio" name="q7" value="4" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                                <label for="q7-4" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">Baik</label>
                                            </div>
                                            <div class="flex items-center">
                                                <input id="q7-5" type="radio" name="q7" value="5" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                                                <label for="q7-5" class="ml-2 text-sm font-medium text-gray-900 dark:text-gray-300">Sangat baik</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Additional Comments -->
                            <div class="border-t border-gray-200 dark:border-gray-600 pt-8">
                                <h4 class="text-lg font-medium text-gray-900 dark:text-white mb-4">
                                    Komentar Tambahan
                                </h4>
                                <textarea id="comments" rows="4" 
                                          class="block w-full p-3 border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:ring-blue-500 focus:border-blue-500" 
                                          placeholder="Silakan tuliskan saran, masukan, atau komentar tambahan Anda..."></textarea>
                            </div>

                            <!-- Submit Button -->
                            <div class="pt-8">
                                <button type="submit" 
                                        class="w-full px-6 py-4 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-medium rounded-lg shadow-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all">
                                    <i class="fas fa-paper-plane mr-2"></i> Kirim Survei
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Privacy Notice -->
                    <div class="bg-blue-50 dark:bg-blue-900 rounded-xl p-6">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <i class="fas fa-lock text-blue-600 dark:text-blue-400 text-xl"></i>
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-medium text-blue-800 dark:text-blue-200">
                                    Kerahasiaan Data
                                </h3>
                                <p class="mt-2 text-blue-700 dark:text-blue-300">
                                    Kami menjamin kerahasiaan data Anda. Informasi yang Anda berikan hanya akan digunakan untuk tujuan evaluasi 
                                    dan perbaikan kualitas pelayanan sesuai dengan ketentuan peraturan perundang-undangan yang berlaku.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>